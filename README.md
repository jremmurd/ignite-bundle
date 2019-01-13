# Pimcore Ignite

Ignite your customer's user experience by adding web realtime functionalities to your web application. [Read more about the Realtime Web](docs/realtime.md). The realtime capabilities are based on the WebSocket technology. To support websocket connections the bundle uses the hosted service [Pusher](https://pusher.com/) by default.

> The bundle is still considered as alpha version.

## Functional Overview:

- Channel system for managing arbitrary events within your application.
    - Three types of channels
        - _private_: authentication required
        - _presence_: authentication required, user information is stored & automatic join/leave events
        - _public_: everyone can join and send events
    - Channels can be assigned one or more transport services called _Drivers_. The bundle is includes three default drivers:
        - _Pusher_: realtime events via a websocket connection [See Pusher documentation for details](https://pusher.com/docs)
        - _Logger_: logged events in pimcore application logger
        - _Notification_: persisted events with assigned data for user notifications
- Client-side code generation for connection establishment and channel subscriptions for specific drivers.
- Show online users and notifications in the Pimcore admin interface, enableable via separate user permissions.


## Components

| Component | Description | Example |
|:-------------|:------------- |:------------- |
| Channel | A specific context for subscriptions. There are three different types of channels: public, private and presence. For details see [Channel Types](https://pusher.com/docs/client_api_guide/client_channels#channel_types).  | global, user, user_julian, product, product_shirt ... |
| Event | At least contains a timestamp and a name. May transport arbitrary data. | Message, Notification, ... |
| Driver | Drivers define the possible transports for the pub/sub events.    | Logger, Pusher, Notification |
| Authenticator | Private and presence channels need authentication, authenticator provide an interface for it. | Firewall specific user authentication i.e. `/admin`, `/app`, ... |
| Radio | Manages all application channels. |  |

## Getting started
- [Installation](./docs/Installation.md)
- [Configuration](./docs/Configuration.md)
- [Code Samples](./docs/Codesamples.md)

Visit `http://your-domain.com/ignite` to see your current configuration and test your realtime capabilities. While still watching your `/ignite` route then call:
- `http://you-domain.com/ignite/publish/public`
- `http://you-domain.com/ignite/publish/presence`
- `http://you-domain.com/ignite/publish/notification`

You should see the messages being added to the html automatically as you call the `/publish/...` routes.

## Impression

In the upper right corner there is the notification widget, which can be enabled via a user permission. 
The widget can be collapsed, repositioned via draggging and resized. 
By clicking a notification item it gets marked as `read` and is removed from the list.

In the lower left corner there is the user persence icon, which shows which users are online by hovering or clicking on the user icon.
![Screenshot](./docs/img/screen_1.PNG "Online Users and Notifications")
