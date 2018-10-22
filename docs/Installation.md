# Installation

Install the `IgniteBundle` via

```
composer require jremmurd/pimcore-ignite-bundle
```

Now if you open the admin interface of your Pimcore application and open the `Extensions` tab in the `Tools` section, the new extension `Pimcore Ignite` should be listed. Click the install button and reload Pimcore.

After the Installation you should see two new user permissions for _user presence_ and _notifications_. Also there is a new file called `ignite.example.yml`in your `app/config/` directory. You may now adjust the file to your needs, rename it to `ignite.yml` and then include it in your `config.yml`. For details on how to configure the extension please see [configuration section](./Configuration.md).
