var IgniteAdmin = IgniteAdmin || {};


IgniteAdmin.Notifications = (function () {

    var $ = jQuery;

    var init = function () {

        if (!Ignite.channels.user_notifications) {
            console.error("Channel user_notifications missing.");
            return;
        }

        initEvents();

        Ext.Ajax.request({
            url: '/admin/ignite/notification/get',
            params: {
                csrf: pimcore.settings['csrfToken']
            }
        })
            .then(function (res) {
                var obj = Ext.decode(res.responseText);
                Ext.each(obj || [], function (event) {
                    showNotification(event);
                })
            });
    };

    var initEvents = function () {
        Ignite.channels.user_notifications.bind('notification', function (event) {
            showNotification(event);
        });
    };

    var showNotification = function (event) {
        var content = getContent(event);
        console.log(event);
        var notification = Ext.create('Ext.window.Toast', {
            iconCls: 'pimcore_icon_apply',
            title: event.title,
            html: content,
            autoShow: true,
            width: 'auto',
            maxWidth: 350,
            closeable: true,
            hideDelay: 6000,
            onDestroy: function () {
                console.log("set read");
                Ext.Ajax.request({
                    url: '/admin/ignite/notification/set-read',
                    params: {
                        csrf: pimcore.settings['csrfToken'],
                        id: event.notification_id
                    }
                })
            }
        });

        notification.show(document);
    };

    var getContent = function (event) {
        try {

            if (!event.data) {
                return event.message;
            }

            var html = event.message + "<br>";

            $(document).on('click', '.ignite-open-object', function () {
                pimcore.helpers.openObject($(this).attr("data-id"));
            });

            if (event.data.related_object.type == "object") {
                html += ("<a class='ignite-open-object' data-id='" + event.data.related_object.data.o_id + "'>Open Object</a>");
            }


            return html;
        } catch (e) {
            console.log(e);
            return "No content.";
        }
    };

    return {
        init: init
    }
})();