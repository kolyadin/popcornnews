/**
 * Profile
 *
 */
var Profile = {

    sel: {},
    config: {},

    prepare: function () {
        Profile.sel = {
            friendshipWrapper: $('.profile-data__options'),
            addFriendship: $('a[data-action=do-friendship-add]', this.sel.friendshipWrapper),
            removeFriendship: $('a[data-action=do-friendship-remove]', this.sel.friendshipWrapper)
        };
    },
    addFriendship: function (friendId) {

        var params = {
            friendId: friendId
        };

        var handler = function (response) {

            var handlerSuccess = function () {
                Profile.sel.addFriendship.parent().find('.profile-data__description').show();
            };

            if (response.status == 'success') {
                handlerSuccess();
            }

        };

        $.post('/ajax/friend/add', params, handler, 'json');
    },
    removeFriendship: function (friendId) {
        var params = {
            friendId: friendId
        };

        var handler = function (response) {

            var handlerSuccess = function () {
                //Profile.sel.removeFriendship.parent().find('.profile-data__description').show();
                location.href = '/profile/' + friendId;
            };

            if (response.status == 'success') {
                handlerSuccess();
            }

        };

        $.post('/ajax/friend/remove', params, handler, 'json');
    },
    bind: function () {
        return {
            friends: function () {
                Profile.sel.addFriendship.on('click', function () {
                    Profile.addFriendship(Profile.config.profileId);

                    return false;
                });

                Profile.sel.removeFriendship.on('click', function () {
                    Profile.removeFriendship(Profile.config.profileId);

                    return false;
                });

            }

        };
    },
    init: function (config) {

        Profile.config = config;
        Profile.prepare();

        Profile.bind().friends();
    }
};