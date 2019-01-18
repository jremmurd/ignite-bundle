# Code Samples

## Server

### Subscribing to channels
```php
    public function indexAction(Request $request, Radio $radio)
    {
        // Presence and Private channels need excplicit configuration, see config.yml 
        $radio
            ->getPresenceChannel("user", ["id" => $this->getUser()->getId()])
            ->subscribe();

        // Public channels can be created without any configuration
        $radio
            ->getPublicChannel("global")
            ->subscribe();
            
        // Channel type will be resolve by config type, if several channel with the same name exist 
        // the priority is as follows: presence -> private -> public
        $radio
            ->getChannel("global")
            ->subscribe();
    }
```

### Publishing events
```php
    public function publishToPublicChannel(Request $request, Radio $radio)
    {
        $global = $radio->getPublicChannel("global");
        $child_1 = $radio->getPublicChannel("global.child_1");    // the dot indicates channel inheritence
        $child_1 = $radio->getPublicChannel("global.child_2");

        $child_1->publish(new Message("Hello Child Channel 1!")); // published to 'global' and 'global.child_1'
        $child_2->publish(new Message("Hello Child Channel 2!")); // published to 'global' and 'global.child_2'

        return new Response("Done.");
    }

    public function publishToPresenceChannel(Request $request, Radio $radio)
    {
        $channel = $radio->getPresenceChannel("user", ["id" => $this->getUser()->getId()]);
        $channel->publish(new Message("Hello Presence Channel!"));

        return new Response("Done.");
    }
    
     public function publishNotifications(Request $request, Radio $radio)
    {
        $radio->setChannelNamespace("admin");
        $notificationChannel = $radio->getPrivateChannel("user_notifications", ["id" => 9999]);

        $element = \Pimcore\Model\DataObject\MyEntity::getById(12345);
        $notificationChannel
            ->publish(new Notification("Yeay!", "Lorem ipsum dolor sit amet!"))
            ->publish(new Notification("Wow!", "Lorem ipsum dolor sit amet!", $element)
    }
```

### Fetching unread notifications
```php
    public function notificationsAction(Request $request){
        $notifications = new Listing();
        
        $notifications->addConditionParam("`read` IS NULL");
        $notifications->addConditionParam("`targetUser` = ?", $this->getUser()->getId());
        $notifications->setLimit(25);
        $notifications->setOrderKey("creationDate");
        $notifications->setOrder("desc");
        
        $this->view->notifications = $notifications;
    }
```

## Client
For details concerning the client side javascript have a look at the [pusher client api guide](https://pusher.com/docs/client_api_guide).
If you use `$myChannel->subscribe()` on the server-side as described the initialization and subscriptions take place automatically as long as you use the `ignite` template helper as described below.


##### index.html.php
```php
<script>
    var Ignite = Ignite || {};
    
    /* output the script for establishing websocket connection and subscriptions/unsubscriptions */
    <?= $this->ignite() ?>

    Ignite.channels.global.bind('message', function (data) {
        // handle event
    });

    Ignite.channels.user.bind('message', function (data) {
        // handle event
    });
    
    Ignite.channels.user.bind('pusher:member_added', function (member) {
        // handle presence channel user event
    });
    
    Ignite.channels.notifications.bind('notification', function (data) {
        // handle event
    });
</script>
```
