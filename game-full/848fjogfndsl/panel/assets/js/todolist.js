(function($) {
    'use strict';
    $(function() {
        var todoListItem = $('.development-items');
        var todoListName = $('.new-task-name');
        var todoListDescription = $('.new-task-description');
        var todoListIcon = $('.new-task-icon');


        $('.todo-list-add-btn').on("click", function(event) {
            event.preventDefault();
            var name = $(this).prevAll('.new-task-name').val();
            var descr = $(this).prevAll('.new-task-description').val();
            var icon = $(this).prevAll('.new-task-icon').val();
            $.get("apis/addDevTask.php", { name: name, description: descr, icon: icon } ).done(function( data ) {
                if (name && descr && icon) {
                    todoListItem.children('div').last().remove();
                    var oldhtml = todoListItem.html();
                    todoListItem.empty();
                    todoListItem.append('<div class="preview-item border-bottom"><div class="preview-thumbnail"><div class="preview-icon bg-danger"><i class="'+icon+'"></i></div></div><div class="preview-item-content d-sm-flex flex-grow"><div class="flex-grow"><h6 class="preview-subject">'+name+'</h6><p class="text-muted mb-0">'+descr+'</p></div><div class="mr-auto text-sm-right pt-2 pt-sm-0"><button class="mb-0 badge badge-danger" style="border-color: #FC424A!important;">Pending</button></div></div></div>' + oldhtml);
                    todoListName.val("");
                    todoListDescription.val("");
                    todoListIcon.val("");
                }
            });

        });

        $('.card-body').on("click", '.status-btn', function(event) {
            var statustxt = this.id.split('status-btn')[1];
            var description = $('#status-txt'+statustxt.toString()).html();
            var status = this.innerHTML;
            switch(status){
                case 'Pending':
                    $.get( "apis/updateTaskStatus.php", { description: description, status: 'workingon' } );
                    this.innerHTML = "In progress";
                    this.className = this.className.replace('badge-danger', 'badge-warning');
                    $('#status-bg'+statustxt).attr('style', 'background-color: #FFAB00 !important');
                    $('#'+this.id).css('border-color', '#FFAB00');
                    break;
                case 'In progress':
                    $.get( "apis/updateTaskStatus.php", { description: description, status: 'complete' } );
                    this.innerHTML = "Complete";
                    this.className = this.className.replace('badge-warning', 'badge-success');
                    $('#status-bg'+statustxt).attr('style', 'background-color: #00D25B !important');
                    $('#'+this.id).css('border-color', '#00D25B');
                    break;
                case 'Complete':
                    $.get( "apis/updateTaskStatus.php", { description: description, status: 'pending' } );
                    this.innerHTML = "Pending";
                    this.className = this.className.replace('badge-success', 'badge-danger');
                    $('#status-bg'+statustxt).attr('style', 'background-color: #FC424A !important');
                    $('#'+this.id).css('border-color', '#FC424A');
                    break;
            }
        });

    });
})(jQuery);