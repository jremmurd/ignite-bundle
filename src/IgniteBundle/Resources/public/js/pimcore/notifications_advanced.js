var IgniteAdmin = IgniteAdmin || {};

IgniteAdmin.Notifications = (function () {

    var defaultWidgetHeight = 320;
    var defaultWidgetWidth = 400;

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

    var windowToggler = false;

    var columns = [
        {text: t("content"), sortable: true, hidden: false, dataIndex: '_content', filter: 'string', flex: 250},
        {text: t("title"), sortable: true, hidden: true, dataIndex: 'title', filter: 'string', flex: 90},
        {text: t("message"), sortable: true, hidden: true, dataIndex: 'message', filter: 'string'},
        {text: t("user"), sortable: true, hidden: true, dataIndex: 'targetUser', flex: 100, filter: 'string',
            renderer: function (v) {
                return v;
            }
        },
        {text: t("date"), sortable: true, hidden: true, name: 'date', dataIndex: 'date', flex: 100, filter: 'date', renderer: GridRenderer.date},
        {
            xtype: 'actioncolumn',
            menuText: t('click_to_open'),
            width: 38,
            id: "notification_open",
            dataIndex: 'elementId',
            items: [{
                tooltip: t('click_to_open'),
                iconCls: "pimcore_icon_open",
                getClass: function(v, meta, rec){
                    if (!v) {
                        return "force-hidden";
                    }
                    return "pimcore_icon_open";
                },
                handler: function (grid, rowIndex, event) {
                    var record = notificationsStore.getAt(rowIndex),
                        id = record.data.elementId,
                        type = record.data.elementType;
                    pimcore.helpers.openElement(id, type, null);
                }.bind(this)
            }]
        },
        {
            xtype: 'actioncolumn',
            menuText: t('click_to_set_read'),
            width: 38,
            items: [{
                tooltip: t('click_to_set_read'),
                iconCls: "pimcore_icon_apply",
                handler: function (grid, rowIndex, event) {
                    notificationsStore.removeAt(rowIndex);
                }.bind(this)
            }],
        }];

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
        collapsed: true,
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
                    if (!windowToggler) {
                        windowToggler = true;
                        notificationWidget.setWidth(600);
                        notificationWidget.setHeight(800);
                        notificationWidget.setX(defaultWidgetPositionX(), false);
                        notificationWidget.setY(defaultWidgetPositionY, false);
                    } else {
                        windowToggler = false;
                        notificationWidget.setWidth(defaultWidgetWidth);
                        notificationWidget.setHeight(defaultWidgetHeight);

                        notificationWidget.setX(defaultWidgetPositionX(), false);
                        notificationWidget.setY(defaultWidgetPositionY, false);
                    }
                }
            }
        ],
        items: {
            xtype: 'grid',
            border: false,
            listeners: {
                cellclick: function (view, td, colIndex, record, tr, rowIndex) {
                    // notificationsStore.removeAt(rowIndex);
                }
            },
            viewConfig: {
                getRowClass: function (record, rowIndex, rowParams, store) {
                    return "multiline-row";
                }
            },
            columns: columns,
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
                id: record.data.notificationId
            },
            failure: function () {
                updateUnreadCount(true);
            }
        })
    };

    var addNotification = function (record) {
        record._content = "<strong>" + record.title + "</strong><br>" + record.message + "<br><small style='float:right'>" + GridRenderer.date(record.creationDate) + "</small>";
        notificationsStore.add(record);
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