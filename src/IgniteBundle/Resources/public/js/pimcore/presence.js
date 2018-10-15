var IgniteAdmin = IgniteAdmin || {};

IgniteAdmin.Presence = (function () {

    var $ = jQuery;

    var init = function () {
        if (!Ignite.channels.user) {
            console.error("Channel 'user' missing.");
            return;
        }

        initEvents();
    };

    var initEvents = function () {
        var users = document.getElementById("ignite-online-users");
        var $users = $("#ignite-online-users");

        Ignite.channels.user.bind('pusher:subscription_succeeded', function (members) {
            users.setAttribute("data-ignite-online-count", members.count ? members.count : 0);
            var memberNames = "";

            var i = 0;

            if (typeof members == "array_map") {
                members.each(function (member) {
                    if (i !== 0) {
                        memberNames += ", "
                    }

                    if (!member.info) {
                        console.log("Missing user info", member);
                        return;
                    }

                    memberNames += ucFirst(member.info.name);
                    i++;
                });
            }

            users.setAttribute("data-menu-tooltip", memberNames);
            $users.removeClass("initialized");
            pimcore.helpers.initMenuTooltips();
        });

        Ignite.channels.user.bind('pusher:member_added', function (member) {
            users.setAttribute("data-ignite-online-count", parseInt(users.getAttribute("data-ignite-online-count")) + 1);
            if (!member.info) {
                return;
            }

            var memberNames = users.getAttribute("data-menu-tooltip") + ", " + ucFirst(member.info.name);
            users.setAttribute("data-menu-tooltip", memberNames);
            $users.removeClass("initialized");
            pimcore.helpers.initMenuTooltips();
        });

        Ignite.channels.user.bind('pusher:member_removed', function (member) {
            users.setAttribute("data-ignite-online-count", parseInt(users.getAttribute("data-ignite-online-count")) - 1);
            var regex = ", " + ucFirst(member.info.name) + "| " + ucFirst(member.info.name);
            regex = new RegExp(regex, "g");

            var memberNames = users.getAttribute("data-menu-tooltip").replace(regex, "");
            users.setAttribute("data-menu-tooltip", memberNames);
            $users.removeClass("initialized");
            pimcore.helpers.initMenuTooltips();
        });

        $('.ignitebundle_icon_users').on("click", function () {
            Ext.MessageBox.alert("Online Users", getWindowContent());
        });
    };

    var getWindowContent = function () {
        var content = "";
        var members = Ignite.channels.user.members;

        if (!members) {
            return "no users";
        }

        content += "<div style='display: flex; align-items: center; justify-content: space-evenly; height: 250px;'>";
        members.each(function (member) {
            content += "<div class='ignite-notificationWindow-member' data-member='" + ucFirst(member.info.name) + "' style='display: flex;align-items: center;flex-direction: column;'>";
            content += ("<img src='/admin/user/get-image?id=" + member.id + "'/>");
            content += "<span>";
            content += ucFirst(member.info.name);
            content += "</span>";
            content += "</div>";
        });
        content += "</div>";

        return content;
    };

    function ucFirst(input) {
        return input[0].toUpperCase() + input.substr(1);
    }

    return {
        init: init
    }
})();