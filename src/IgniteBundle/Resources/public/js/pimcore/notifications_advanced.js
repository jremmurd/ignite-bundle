var IgniteAdmin = IgniteAdmin || {};

IgniteAdmin.Notifications = (function () {

    var defaultWidgetHeight = 320;
    var defaultWidgetWidth = 280;

    var defaultWidgetPositionY = 34;
    var defaultWidgetPositionX = function () {
        if (notificationWidget) {
            return pimcore.viewport.width - notificationWidget.width - 10;
        } else {
            return pimcore.viewport.width - defaultWidgetWidth - 10;
        }
    };

    var GridRenderer = {
        date: function (d) {
            var date = new Date(d * 1000);
            return Ext.Date.format(date, "Y-m-d H:i:s");
        }
    };

    var notificationsStore = Ext.create('Ext.data.ArrayStore', {
        listeners: {
            remove: function (store, records) {
                removeNotification(records[0]);
            }
        }
    });

    var notificationWidget = Ext.create('Ext.window.Window', {
        title: "Notifications",
        height: defaultWidgetHeight,
        width: defaultWidgetWidth,
        cls: "ignite-notification-window",
        x: defaultWidgetPositionX(),
        y: defaultWidgetPositionY,
        collapsible: true,
        shadow: false,
        resizeable: true,
        closable: true,
        hideCollapseTool: true,
        titleCollapse: true,
        // alwaysOnTop: true,
        collapsed: false,
        layout: 'fit',

        tools: [
            {
                type: 'save',
                tooltip: "Set all read",
                handler: function (event, toolEl, panelHeader) {
                    notificationsStore.getData().items.forEach(function (record) {
                        removeNotification(record);
                    });
                    notificationsStore.removeAll();
                    notificationWidget.setCollapsed(true);
                }
            },
            {
                type: 'pin',
                tooltip: 'Restore window position',
                handler: function (event, toolEl, panelHeader) {
                    notificationWidget.setX(defaultWidgetPositionX(), false);
                    notificationWidget.setY(defaultWidgetPositionY, false);
                }
            },
            {
                type: 'maximize',
                tooltip: 'Maximize',
                handler: function (event, toolEl, panelHeader) {
                    notificationWidget.setWidth(800);
                    notificationWidget.setHeight(800);
                    notificationWidget.setX(defaultWidgetPositionX(), false);
                    notificationWidget.setY(defaultWidgetPositionY, false);
                }
            },
            {
                type: 'minimize',
                tooltip: 'Minimize',
                handler: function (event, toolEl, panelHeader) {
                    notificationWidget.setWidth(defaultWidgetWidth);
                    notificationWidget.setHeight(defaultWidgetHeight);
                    notificationWidget.setX(defaultWidgetPositionX(), false);
                    notificationWidget.setY(defaultWidgetPositionY, false);
                }
            }
        ],
        items: {
            xtype: 'grid',
            border: false,
            listeners: {
                cellclick: function (view, td, colIndex, record, tr, rowIndex) {
                    notificationsStore.removeAt(rowIndex);
                }
            },
            viewConfig: {
                getRowClass: function (record, rowIndex, rowParams, store) {
                    return "multiline-row";
                }
            },
            columns: [
                {text: t("Content"), sortable: true, hidden: false, dataIndex: 'content', filter: 'string', flex: 250},
                {text: t("title"), sortable: true, hidden: true, dataIndex: 'title', filter: 'string', flex: 90},
                {text: t("message"), sortable: true, hidden: true, dataIndex: 'message', filter: 'string'},
                {
                    text: t("element"), sortable: false, dataIndex: 'cpath', flex: 200, hidden: true,
                    renderer: function (value, metaData, record, rowIndex, colIndex, store) {
                        // TODO
                        // if (record.get("cid")) {
                        //     return t(record.get("ctype")) + ": " + record.get("cpath");
                        // }
                        // return "";
                    }
                },
                {
                    text: t("user"), sortable: true, hidden: true, dataIndex: 'user', flex: 100, filter: 'string',
                    renderer: function (v) {
                        // TODO
                        return v;
                        // if (v && v["name"]) {
                        //     return v["name"];
                        // }
                        // return "";
                    }
                },
                {
                    text: t("date"),
                    sortable: true,
                    hidden: true,
                    name: 'date',
                    dataIndex: 'date',
                    flex: 100,
                    filter: 'date',
                    renderer: GridRenderer.date
                }
            ],
            store: notificationsStore,
            whiteSpace: 'normal'
        }
    });

    var updateUnreadCount = function (doAdd) {
        var title = notificationWidget.getTitle();
        var number = parseInt(title.replace(/^\D+/g, ''));

        if (isNaN(number)) {
            number = 0;
        }

        if (!doAdd) {
            number -= 1;
        } else {
            number += 1;
        }

        var text = number > 0 ? "(" + number + ") " : "";
        text += "Notifications";

        notificationWidget.setTitle(text);
    };

    var init = function () {
        if (!Ignite.channels.user_notifications) {
            console.error("Channel user_notifications missing.");
            return;
        }

        notificationWidget.setCollapsed(true);
        notificationWidget.show();

        pimcore.viewport.add(notificationWidget);

        /* listen to new notifications */
        initEvents();

        /* get saved unread notifications */
        Ext.Ajax.request({
            url: '/admin/ignite/notification/get',
            params: {
                csrf: pimcore.settings['csrfToken']
            }
        }).then(function (res) {
            var obj = Ext.decode(res.responseText);
            Ext.each(obj || [], function (event) {
                addNotification(event);
            })
        });
    };

    var initEvents = function () {
        Ignite.channels.user_notifications.bind('notification', function (event) {
            addNotification(event);
        });
    };

    var removeNotification = function (record) {
        updateUnreadCount();

        Ext.Ajax.request({
            url: '/admin/ignite/notification/set-read',
            params: {
                csrf: pimcore.settings['csrfToken'],
                id: record.data.notification_id
            },
            failure: function () {
                updateUnreadCount(true);
            }
        })
    };

    var addNotification = function (record) {
        notificationsStore.add({
            content: "<strong>" + record.title + "</strong><br>" + record.message + "<br><small style='float:right'>" + GridRenderer.date(record.creationDate) + "</small>",
            message: record.message,
            title: record.title,
            date: record.creationDate,
            notification_id: record.notification_id,
        });

        notificationsStore.sort('date', 'DESC');
        updateUnreadCount(true);
    };

    return {
        init: init,
        getNotificationStore: notificationsStore,
        addNotification: addNotification,
        removeNotification: removeNotification
    }
})();