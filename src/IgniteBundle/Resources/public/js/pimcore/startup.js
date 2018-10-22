pimcore.registerNS("pimcore.plugin.IgniteBundle");

pimcore.plugin.IgniteBundle = Class.create(pimcore.plugin.admin, {
    getClassName: function () {
        return "pimcore.plugin.IgniteBundle";
    },

    initialize: function () {
        pimcore.plugin.broker.registerPlugin(this);
    },

    pimcoreReady: function (params, broker) {

        this.initIgnite();
    },

    initIgnite: function () {
        var user = pimcore.globalmanager.get("user");

        Ext.Ajax.request({
            url: '/admin/ignite/init',
            params: {
                csrf: pimcore.settings['csrfToken']
            }
        })
            .then(function (res) {
                var obj = Ext.decode(res.responseText);
                window.dispatchEvent(new Event("ignite:init"));

                if (!obj.script) {
                    return;
                }

                jQuery('head').append("<script>" + obj.script + "<\/script>");

                if (user.isAllowed("bundle_ignite_presence")) {
                    jQuery.getScript("/bundles/ignite/js/pimcore/presence.js")
                        .done(function () {
                            Ext.get("pimcore_status").insertHtml('beforeEnd',
                                '<div class="ignitebundle_icon_users" data-ignite-online-count="0" id="ignite-online-users" data-menu-tooltip="" style="margin-top:10px"></div>'
                            );

                            IgniteAdmin.Presence.init();
                        });
                }

                if (user.isAllowed("bundle_ignite_notifications")) {
                    jQuery.getScript("/bundles/ignite/js/pimcore/notifications_advanced.js")
                        .done(function () {
                            IgniteAdmin.Notifications.init();
                        });
                }

                Ignite.channels.admin.bind("pusher:subscription_error", function (e) {
                    console.error(e);
                });
            });
    }
});

var IgniteBundlePlugin = new pimcore.plugin.IgniteBundle();