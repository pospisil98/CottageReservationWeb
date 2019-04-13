<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>PHP Week Reservation Table</title>
    <meta name="generator" content="BBEdit 9.6" />
    <link href='http://fonts.googleapis.com/css?family=Raleway:400,100,900' rel='stylesheet' type='text/css'>
    <style type="text/css" media="screen">
        * {margin:0; padding:0; font-weight: 400; font: normal 16px/1.2em 'Raleway', sans-serif; color: #362b27;}
        body {background: url(glamourshot.jpg) center center no-repeat #000; background-attachment: fixed;}
        h2 {font-size: 500%; font-weight: 100; line-height: .9em; }
        h3 {font-size: 110%; color: #c8801a; padding: 12px 0 4px 0; font-weight: 900;}
        h5 {font-size: 170%; font-weight: 900; color: #c8801a;}
        b, strong {font-weight: 900;}
        a {text-decoration: underline;}
        s, span {font: inherit; color: inherit;}
        li {list-style: square; list-style-position: outside; margin-left: 1em; }
        span {color: #c8801a;}
        table {margin: 0 auto;}
        th, th label {font-weight: bold;}
        td { padding: 4px 0px;}
        input[type=email] {border: 2px solid #c8801a; padding: 2px 4px;}
        input[type=submit] {background: #c8801a; color: #fff; padding: 4px 12px; border-radius: 12px;}
        .window {background: #fff; padding: 60px 65px; border-radius: 0 120px 0 120px; width: 850px; margin: 120px auto; clear: both; min-height:480px;}
        .window:hover {}
        .reservation label { padding: 4px 14px; border-radius: 16px; }
        .reservation td {text-align:center;}
        .reservation a {border-radius: 20px; background:#c8801a;padding: 6px 12px;color:white; text-decoration: none; font-weight: bold;}
        .reservation h3 {font-size: 250%;}
        .avail {background : #bcd200;}
        .avail:hover { background: #def100; color:#c00;}
        .hold {background: #c00;color:#ff0;}
        .hold:hover {background: #900;}
        .closed {color: #999; background: white; text-decoration: line-through;}
        .closed input, .reserved input {display: none;}
        .reserved {background : #c8801a; color: white; text-decoration: line-through;}
        .daily { border: thin solid black; border-bottom: black thick solid;}
        .drivers {font-size: 75%;}
        .biggie {font-size: 150%; font-weight: bold; text-shadow: -1px -1px #fff; color: #090; background: #8eff51; text-align: center; float: right; clear: both; border-radius: 16px; text-decoration: none; padding: 6px 12px; border:3px solid #090; border-bottom: 8px solid #090; margin: 5px auto;}
    </style>
</head>

<body>
<a name="reserve"></a>
<div class="window">
    <h2>PHP Display Weekly Reservation Calendar</h2>
    <p>A simple PHP loop creating a table with checkboxes & date/time form elements in each cell.  Each cell represents one hour, and we list the hours between 9am and 9pm.  The table should display one week, starting on a Monday.  It should output something similar to the below HTML.</p>
    <p>I'd like to be able to set a few variables: Startdate (will default to the Monday before this date).  If I call the startdate for a Saturday, it'll display this week starting with Monday and today being the second-to-last available day.</p>
    <p>When looping and making the table, you should be able to set inline styles for table cells that are Unavailable (items in the past of right now) and Reserved (a comma-delimited list of date/times).</p>
    <p>We'll be collecting the checkboxed entries as time slots for an hour-by-hour reservation system.  This project is simply the table output for the form.  I appreciate tight loops and clean code.  This is my first project with Bountify, and I look forward to all the creative responses!</p>

    <?php
    // http://sitename/index.php?startdate=201303011200&reserved=201303011100,201303011500,201303011600

    // Format strings
    $timeFormat = 'g:iA'; // 9:00AM
    $weekdayFormat = 'l'; // Monday
    $dateFormat = 'F j'; // April 1
    $valueFormat = 'YmdHi'; // 201303180900
    $now = time();
    // Get start date
    if(isset($_GET['startdate'])) {
        $currentTime = strtotime($_GET['startdate']);
    } else {
        $currentTime = $now; // This week
    }
    // Get reserved hours
    $reserved = array();
    if(isset($_GET['reserved'])) {
        $a = explode(' ', preg_replace('/\s{2,}|[^\s\d]/', ' ', $_GET['reserved']));
        foreach($a as $time) {
            $reserved[] = strtotime($time);
        }
    }

    // Get timestamps
    $startingTime = strtotime('last monday +9 hours', $currentTime); // current week, Monday, 9AM
    $endingTime = strtotime('next sunday', $startingTime);
    $startingMonth = date('F', $startingTime);
    $endingMonth = date('F', $endingTime);
    $startingDay = date('j', $startingTime);
    $endingDay = date('j', $endingTime);
    // Week of ...
    $period = $startingMonth . ' ' . $startingDay . ' - ';
    if($startingMonth != $endingMonth) {
        $period .= $endingMonth . ' ';
    }
    $period .= $endingDay;
    ?>

    <form action="/" method="POST">
        <table cellpadding="0" cellspacing="4" border="0" class="reservation">
            <tr>
                <td><h3><a href="">&lt;</a></h3></td>
                <td colspan=5><h3>Week of <?php echo $period?></h3></td>
                <td><h3><a href="">&gt;</a></h3></td>
            </tr>

            <?php
            // Table header
            echo '<tr>';
            for($i = 0; $i < 7; $i++) {
                $time = strtotime("+$i days", $startingTime);
                echo '<th><label>' . date($weekdayFormat, $time)  . '</label></th>';
            }
            echo '</tr>';

            for($h = 0; $h < 12; $h++) {
                echo '<tr>';
                for($i = 0; $i < 7; $i++) {
                    $time = strtotime("+$i days $h hours", $startingTime);
                    $value = date($valueFormat, $time);
                    if($time <= $now) {
                        $class = 'closed';
                    } else {
                        $class = (in_array($time, $reserved)) ? 'reserved': 'avail';
                    }
                    $text = date($timeFormat, $time);
                    echo "<td><label class='$class'><input type='checkbox' name='dates[]' value='$value' />$text</label></td>";
                }
                echo '</tr>';
            }
            // Table footer
            echo '<tr>';
            for($i = 0; $i < 7; $i++) {
                $time = strtotime("+$i days", $startingTime);
                echo '<th><label>' . date($dateFormat, $time)  . '</label></th>';
            }
            echo '</tr>';
            ?>
        </table>
        <center><input type="submit" /></center>
    </form>
</div>
</body>
</html>