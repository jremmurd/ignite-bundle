# Configuration
For details please refere to the comments in the provided `config.yml` example.

##### config.yml
```yaml
ignite:
  drivers:
  
    pusher:
      service_id: JRemmurd\IgniteBundle\Ignite\Driver\Pusher 
      config:
        key: "pusher-application-key"
        secret: "pusher-application-secret"
        id: "pusher-application-id"
        options:
          cluster: eu
        log_to_console: true
        
    logger:
      service_id: JRemmurd\IgniteBundle\Ignite\Driver\Logger   # logs to Pimcore Application Logger 
      
    notification:
      service_id: JRemmurd\IgniteBundle\Ignite\Driver\Notification   # persists events into database
      
  channels:   
    strict_parameters: true                                   # channel parameters have to be configured
    default_driver_name: pusher                               # if neither the namespace nor the channel has a driver configured, the global default driver will be used
    factory_id: ignite.channel_factory                        # factory service id
    
    namespaces:
    
      admin:
        default_driver_name: pusher                           # for each namespace a default driver can be set
        auth_endpoint: /admin/ignite/auth
        channels:
        
          presence:
            - name: user                                      # name of the channel
              authenticator: ignite.authenticator.pimcore     # authenticator service id
          
          private:
            - name: user_notifications
              authenticator: ignite.authenticator.pimcore
              drivers: [pusher, notification]                 # one or several drivers can be configured
              parameters:
              - id
      app:
        pattern: ^/ignite
        auth_endpoint: /ignite/auth/{driver}
        channels:   
               
          public:                                             # public channels do not necessarily be configured
            - name: global
              drivers: [logger, pusher]                       # several drivers can be configured for each channel
              parameters:                                     # only valid js variable characters --> used for building the channel name
                - id
                
          private:                                            # configuration for authenticated channels is required
            - name: notifications
              drivers: [pusher, notifications]                
          
          presence:                                           # max 100 users for Pusher driver 
            - name: user
              use_slug_for_js: false                          # whether the parameters should be included in the js channel variable names: 'user' vs. 'user_12345'
              parameters:                                     
                - id
              authenticator: ignite.authenticator.user
              drivers: [logger, pusher]
```