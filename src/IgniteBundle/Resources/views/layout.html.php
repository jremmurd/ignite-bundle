<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 15.04.2018
 * Time: 18:41
 *
 * @var \Pimcore\Templating\PhpEngine $this
 * @var \Pimcore\Templating\PhpEngine $view
 * @var \Pimcore\Templating\GlobalVariables $app
 */
?>
<!DOCTYPE html>
<html>
<head>

    <title>IGNITE PIMCORE</title>
    <link href="/static/css/bootstrap.min.css" rel="stylesheet">

    <script
            src="https://code.jquery.com/jquery-3.3.1.min.js"
            integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
            crossorigin="anonymous"></script>
    <script src="/bundles/ignite/js/libs/pusher-4.1.min.js"></script>

</head>

<body>

<?php $this->slots()->output("_content") ?>

<?= $this->inlineScript() ?>

</body>

</html>