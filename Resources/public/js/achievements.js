function achievementUnlocked(category, id, name, description)
{
    var container = document.createElement('div');
    $(container).addClass('achievement_unlocked');

    var content = '<div class="title">' + name + '</div>';
    content += '<div class="image" style="background-image: url(\'bundles/cunningsoftachievement/images/achievement/' + category + '/' + id + '.png\')"></div>';
    content += '<div class="description">' + description + '</div>';

    $(container).html(content);
    $('body').append(container);

    $(container).dialog({
        width: 500,
        height: 300,
        resizable: false,
        buttons: {
            'OK': function() {
                $.post(category + '/' + id + '/markAchievementMessageShown');
                $(this).dialog('close');
            }
        },
        dialogClass: 'achievementUnlocked'
    });
}

function switchAchievementCategory(id)
{
    $('.achievement_content .category').hide();
    $('#achievement_category_' + id).show();
    $('.achievement_menu').removeClass('selected');
    $('#achievement_menu' + id).addClass('selected');
}
