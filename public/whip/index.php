<!DOCTYPE html>
<?php
    function cmd($command)
    {
        exec($command, $output);
        return $output;
    }

    $action = $_GET['action'] ?? null;
    if (in_array($action, ['start', 'stop', 'restart'], true)) {
        $result = cmd('sudo /etc/init.d/botty ' . $action);
    }
?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="I am in each channel on slack, even if you do not see me. Talk to me by start a message with @T3Bot or with the command prefix.">
    <meta name="author" content="Frank Nägler">
    <link rel="apple-touch-icon" sizes="57x57" href="/Resources/Public/Assets/apple-touch-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/Resources/Public/Assets/apple-touch-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/Resources/Public/Assets/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/Resources/Public/Assets/apple-touch-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/Resources/Public/Assets/apple-touch-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/Resources/Public/Assets/apple-touch-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/Resources/Public/Assets/apple-touch-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/Resources/Public/Assets/apple-touch-icon-152x152.png">
    <link rel="icon" type="image/png" href="/Resources/Public/Assets/favicon-196x196.png" sizes="196x196">
    <link rel="icon" type="image/png" href="/Resources/Public/Assets/favicon-160x160.png" sizes="160x160">
    <link rel="icon" type="image/png" href="/Resources/Public/Assets/favicon-96x96.png" sizes="96x96">
    <link rel="icon" type="image/png" href="/Resources/Public/Assets/favicon-16x16.png" sizes="16x16">
    <link rel="icon" type="image/png" href="/Resources/Public/Assets/favicon-32x32.png" sizes="32x32">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/Resources/Public/Assets/mstile-144x144.png">

    <title>T3Bot</title>

    <!-- Bootstrap core CSS -->
    <link href="/Resources/Public/Css/bootstrap.min.css" rel="stylesheet">
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">


    <!-- Custom styles for this template -->
    <link href="/Resources/Public/Css/cover.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
<a href="https://github.com/NeoBlack/T3Bot"><img style="position: absolute; top: 0; right: 0; border: 0;" src="https://camo.githubusercontent.com/52760788cde945287fbb584134c4cbc2bc36f904/68747470733a2f2f73332e616d617a6f6e6177732e636f6d2f6769746875622f726962626f6e732f666f726b6d655f72696768745f77686974655f6666666666662e706e67" alt="Fork me on GitHub" data-canonical-src="https://s3.amazonaws.com/github/ribbons/forkme_right_white_ffffff.png"></a>
<div class="site-wrapper">
    <div class="site-wrapper-inner">
        <div>
            <div class="inner cover">
                <h1>Botty Control Center</h1>
                <a href="index.php?action=start" class="btn btn-success col-lg-4">Start</a>
                <a href="index.php?action=restart" class="btn btn-warning col-lg-4">Restart</a>
                <a href="index.php?action=stop" class="btn btn-danger col-lg-4">Stop</a>
                <?php if ($result !== null) {
    ?>
                    <div class="result" style="text-align: left;">
<pre><?php foreach ($result as $row) {
        ?>
<?= $row . chr(10) ?>
<?php
    } ?></pre>
                    </div>
                <?php 
} ?>
            </div>
            <div class="mastfoot">
                <div class="inner">
                    <p>Cover template for <a href="http://getbootstrap.com">Bootstrap</a>, by <a href="https://twitter.com/mdo">@mdo</a>.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="/Resources/Public/JavaScript/bootstrap.min.js"></script>
<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
<script src="/Resources/Public/JavaScript/ie10-viewport-bug-workaround.js"></script>
</body>
</html>