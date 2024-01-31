#! /usr/bin/bash

Prog_Name=$( /usr/bin/basename    "$0"        )	|| exit 1
Dir_Name=$(  /usr/bin/dirname     "$0"        )	|| exit 1
Dir_Name=$(  /usr/bin/realpath -s "$Dir_Name" )	|| exit 1

This_File="$Dir_Name""/""$Prog_Name"

function	get_text()
{
	Text_Name="$1"
	Text_Started="0"

	while read Line; do

		if [ "$Text_Started" -eq "1" ]; then

			if [ "$Line" == "_EOF_""$Text_Name" ]; then
				Text_Started="0"
			else
				echo "$Line"
			fi

		else
			[ "$Line" == "_SOF_""$Text_Name" ] && Text_Started="1"
		fi

	done <<< $( /usr/bin/cat "$This_File" )
}

function	User_Report()
{

	User="$1"

	EMail_Address=$(	/usr/bin/cat << _EOF	| /usr/bin/mysql "$dbName" --disable-column-names
					SELECT		email
					FROM		users
					WHERE		users.loginname		= "$User"
					AND		users.deleted		= 'N'
					AND		users.priority		BETWEEN 4000 AND 4099
					ORDER BY	users.priority		DESC
					LIMIT					1;
_EOF
	)

	if [ -z $EMail_Address ]; then
		echo "$Prog_Name: Ignoring user $User. No suitable E-Mail Address found"			 >&2
		return;
	fi

	Temp_File=$( /usr/bin/mktemp --tmpdir="$Temp_Dir" "MailZu-Report-XXXXXXXX" )

	get_text "header_message" | /usr/bin/sed s/Number_of_Days/$Number_of_Days/				 > "$Temp_File"

	OLD_IFS="$IFS"
	IFS='	'
	Line_Counter="0"

	while read Line; do

		Time="1970-01-01 00:00:00"
		Sender="None"
		Recipient="None"
		Subject="None"

		Line_Counter=$(( Line_Counter +  1 ))
		Counter="1"

		for Field in $Line; do

			[ "$Counter" -eq "1" ] && Time="$Field"
			[ "$Counter" -eq "2" ] && Sender="$Field"
			[ "$Counter" -eq "3" ] && Recipient="$Field"
			[ "$Counter" -eq "4" ] && Subject="$Field"

			Counter=$(( Counter + 1 ))
	
		done

		/usr/bin/printf "%s\t%-30.30s\t%-30.30s\t%s\n" "$Time" "$Sender" "$Recipient" "$Subject"	>> "$Temp_File"

	done

	IFS="$OLD_IFS"

	get_text trailer_message	>> "$Temp_File"

	Send_Report="1"
	if [ "$Line_Counter" -eq "0" ]; then

		if [ "$Send_Zero" -eq "1" ]; then
			get_text "zero_message" | /usr/bin/sed s/Number_of_Days/$Number_of_Days/		 > "$Temp_File"
		else
			Send_Report="0"
		fi

	fi

	if [ "$Send_Report" -eq "1" ]; then

		if [ ! -x /usr/bin/txt2html -o "$Text_Email" -eq "1" ]; then
			/usr/bin/cat "$Temp_File" 										|\
			/usr/bin/mail -s "MailZu Quarantine Report" "$EMail_Address"
		else
			Temp_HTML_File=$( /usr/bin/mktemp --tmpdir="$Temp_Dir" "MailZu-Report-XXXXXXXX.html" )

			if /usr/bin/txt2html $"$Temp_File" > "$Temp_HTML_File" 2>/dev/null; then
				/usr/bin/cat "$Temp_HTML_File"									|\
				/usr/bin/mail -a "Content-type: text/html" -s "MailZu Quarantine Report" "$EMail_Address"
			else
				/usr/bin/cat "$Temp_File" 									|\
				/usr/bin/mail -s "MailZu Quarantine Report" "$EMail_Address"
			fi

			/usr/bin/rm "$Temp_HTML_File"

		fi
	fi

	/usr/bin/rm "$Temp_File"
}

function	Per_User()
{
	while read User; do

		/usr/bin/cat << _EOF	| /usr/bin/mysql "$dbName" --disable-column-names	| User_Report "$User"
			SELECT		msgs.time_iso,
					msgs.from_addr,
					recip.email,
					msgs.subject
			FROM		msgs		INNER JOIN	msgrcpt			ON msgs.mail_id=msgrcpt.mail_id
							LEFT JOIN	maddr	AS recip	ON msgrcpt.rid=recip.id
			WHERE		recip.email	IN		(	SELECT	users.email
										FROM	users
										WHERE	users.loginname="$User"
									)
			AND		msgrcpt.content	IN		( '', 'V', 'B', 'S', 'H' )
			AND		msgrcpt.rs	IN		( '', 'P'                )
			AND		msgs.time_iso	>=		DATE(NOW()) + INTERVAL 0 SECOND - INTERVAL $Number_of_Days DAY
			AND		msgs.time_iso	<		DATE(NOW()) + INTERVAL 0 SECOND
			ORDER BY	msgs.time_num			DESC;
_EOF

	done
}

if [ ! -r "$Dir_Name""/../config/config.php" ]; then
	echo "$Prog_Name: file $Dir_Name/../config/config.php not found or not readable"
	exit 1
fi

Counter=0

while read Line; do

	[ "$Counter" -eq "0" ] && dbType="$Line"
	[ "$Counter" -eq "1" ] && dbUser="$Line"
	[ "$Counter" -eq "2" ] && dbPass="$Line"
	[ "$Counter" -eq "3" ] && dbName="$Line"
	[ "$Counter" -eq "4" ] && hostSpec="$Line"
	[ "$Counter" -eq "5" ] && Number_of_Days="$Line"
	[ "$Counter" -eq "6" ] && Send_Zero="$Line"
	[ "$Counter" -eq "7" ] && Text_Email="$Line"

	Counter=$(( Counter + 1 ))

done <<< $(	cd $Dir_Name
		/usr/bin/php -r	'	include( "../config/config.php" );
					print (isset( $conf["db"    ]["dbType"    ] ) ? $conf["db"    ]["dbType"    ] : "") . "\n";
					print (isset( $conf["db"    ]["dbUser"    ] ) ? $conf["db"    ]["dbUser"    ] : "") . "\n";
					print (isset( $conf["db"    ]["dbPass"    ] ) ? $conf["db"    ]["dbPass"    ] : "") . "\n";
					print (isset( $conf["db"    ]["dbName"    ] ) ? $conf["db"    ]["dbName"    ] : "") . "\n";
					print (isset( $conf["db"    ]["hostSpec"  ] ) ? $conf["db"    ]["hostSpec"  ] : "") . "\n";
					print (isset( $conf["script"]["reportDays"] ) ? $conf["script"]["reportDays"] :  3) . "\n";
					print (isset( $conf["script"]["sendZero"  ] ) ? $conf["script"]["sendZero"  ] :  0) . "\n";
					print (isset( $conf["script"]["textEmail" ] ) ? $conf["script"]["textEmail" ] :  0) . "\n";	' 
	)

digit_regex="^[[:digit:]]+$"

if [[ ! "$Number_of_Days" =~ $digit_regex  ]]; then
	echo "$Prog_Name: \$conf[\"script\"][\"reportDays\"] is not an non-negative integer"
	exit 1
fi
if [ "$Number_of_Days" -lt "0" ]; then
	echo "$Prog_Name: \$conf[\"script\"][\"reportDays\"] is not an non-negative integer"
	exit 1
fi

[ "$Number_of_Days" -eq "0" ] && exit 0

if [[ ! "$Send_Zero" =~ $digit_regex  ]]; then
	echo "$Prog_Name: \$conf[\"script\"][\"sendZero\"] should be '0' or '1'"
	exit 1
fi
if [ "$Send_Zero" -lt "0" -o "$Send_Zero" -gt "1" ]; then
	echo "$Prog_Name: \$conf[\"script\"][\"sendZero\"] should be '0' or '1'"
	exit 1
fi

if [[ ! "$Text_Email" =~ $digit_regex  ]]; then
	echo "$Prog_Name: \$conf[\"script\"][\"textEmail\"] should be '0' or '1'"
	exit 1
fi
if [ "$Text_Email" -lt "0" -o "$Text_Email" -gt "1" ]; then
	echo "$Prog_Name: \$conf[\"script\"][\"textEmail\"] should be '0' or '1'"
	exit 1
fi

if [ "$dbType" != "mysql" ]; then
	echo "$Prog_Name: Database type $dbType not supported"
	exit 1
fi

# Set default port number
dbPort="3306"

Counter=0

for Field in $( IFS=':'; echo $hostSpec ); do

	[ "$Counter" -eq "0" ] && dbHost="$Field"
	[ "$Counter" -eq "1" ] && dbPort="$Field"

	Counter=$(( Counter + 1 ))

done

if [[ ! "$dbPort" =~ $digit_regex  ]]; then
	echo "$Prog_Name: \$conf[\"db\"][\"hostSpec\"] port number is not a positive integer"
	exit 1
fi
if [ "$dbPort" -le "0" ]; then
	echo "$Prog_Name: \$conf[\"db\"][\"hostSpec\"] port number is not a positive integer"
	exit 1
fi

if ! Temp_Dir=$( /usr/bin/mktemp --directory --tmpdir="/tmp" "MailZu-Dir-XXXX" ); then
	echo "$Prog_Name: failed to create a temporary directory"
	exit 1
fi

(
	cd $Temp_Dir
	export HOME="$Temp_Dir"

	/usr/bin/cat << _EOF	> .my.cnf

	[client]
	host		= $dbHost
	port		= $dbPort
	user		= $dbUser
	password	= $dbPass

_EOF

	/usr/bin/cat << _EOF	| /usr/bin/mysql "$dbName" --disable-column-names	| Per_User
		SELECT	DISTINCT	users.loginname
		FROM			users
		WHERE			users.priority BETWEEN 4000 AND 4099;
_EOF
)

/usr/bin/rm -rf "$Temp_Dir"

exit 0

_SOF_header_message
Dear MailZu User,

The following un-reviewed e-mail messages, received over the last Number_of_Days days, are being held in quarantine:

Date       Time         Sender                          Recipient                       Header
---------- ------------ ------------------------------- ------------------------------- -------------------------------
_EOF_header_message

_SOF_trailer_message

Unreleased messages will be discarded automatically.

With kind regards,

The MailZu
_EOF_trailer_message

_SOF_zero_message
Dear MailZu User,

No un-reviewed e-mail messages, received over the last Number_of_Days days, are being held in quarantine.

With kind regards,

The MailZu
_EOF_zero_message
