<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 15.04.2018
 * Time: 21:15
 *
 * @var \Pimcore\Templating\PhpEngine $this
 * @var \Pimcore\Templating\PhpEngine $view
 * @var \Pimcore\Templating\GlobalVariables $app
 */

$this->extend("IgniteBundle::layout.html.php");

$script = (string)$this->ignite();
?>
<div class="container">
    <div class="card m-3 p-4">
        <h1>Pimcore Ignite</h1>
    </div>

    <div class="card m-3 p-4">
        <h5>Current Config </h5>
        <?php
        p_r(Pimcore::getContainer()->getParameter("ignite.config"));
        ?>

        <h5 class="mt-3">Generated Script</h5>
        <pre>
        <?= ($script) ?>
        </pre>
    </div>

    <div class="card  m-3 p-4">
        <h1>Messages</h1>

        <h4 class="mt-4">Global Channel</h4>
        <div class="channel channel-global list-group">
        </div>
        <hr>

        <h4 class="mt-4">User Channel</h4>
        <div class="channel channel-user list-group">
        </div>

    </div>
    <div class="card  m-3 p-4">

        <h1>Notifications
            <small>(not realtime)</small>
        </h1>
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Message</th>
                <th>Status</th>
                <th>Channel</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($this->notifications as $notification):
                /* @var JRemmurd\IgniteBundle\Model\Notification $notification */
                ?>

                <tr>
                    <td><?= $notification->getId() ?></td>
                    <td><?= $notification->getTitle() ?></td>
                    <td><?= $notification->getMessage() ?></td>
                    <td><?= $notification->isRead() ? "read" : "unread" ?></td>
                    <td><?= json_encode($notification->getData(), JSON_PRETTY_PRINT) ?> </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>

<script>

    var Ignite = Ignite || {};

    <?= $script ?>

    var $userContent = $(".channel-user");
    var $globalContent = $(".channel-global");

    Ignite.channels.global.bind('message', function (data) {
        $globalContent.prepend("<p class='list-group-item'>" + JSON.stringify(data) + "</p>")
    });

    Ignite.channels.user.bind('message', function (data) {
        $userContent.prepend("<p class='list-group-item'>" + JSON.stringify(data) + "</p>")
    });
</script>