
var folderObject;
var bucketObject;

/**
 * Once the page is loaded, render the files within the root folder
 */
$(document).ready( function() {
    /**
     * TOOLSIPS
     */
    $('[data-toggle="tooltip"]').tooltip();

    blocker();

    $.get('/s3mediamanager/default/get-bucket-object', function(data) {
        var obj = JSON.parse(data);
        bucketObject = JSON.parse(obj.bucketObject);

        createJsTree(obj.folderObject);

        for ( var file in bucketObject['/'] )
        {
            $('#files').append(buildFileRow(
                bucketObject['/'][file].icon, 
                bucketObject['/'][file].text, 
                bucketObject['/'][file].id, 
                bucketObject['/'][file].modified, 
                convertSize(bucketObject['/'][file].size)
                ));
        }

        $('#mm__wrapper').unblock();
    });  

    /** Modal Stuff */
    var opener;

    $('.modal').on('show.bs.modal', function(e) {
        opener = document.activeElement;
    });

    $('#insertFile').click(function(){
        var target = $(opener).parent().parent().find('input').attr('id');
        $('#'+target).val($('#selectedFile').val());
        $('#MediaManager').modal('hide');
    });
});


function createJsTree(data)
{
    $('#folderTree').jstree({
        'core' : {
            'data' : data,
            'check_callback' : true,
        },
        'plugins' : [
            ['contextmenu']
        ],
        'contextmenu' : {
            'items' : function(node) {
                var items = $.jstree.defaults.contextmenu.items();
                items.ccp = false;
                items.rename = false;

                return items;
            }
        }
    }).bind('rename_node.jstree', function(e, data) {
        blocker();
        $.post('/s3mediamanager/default/create-folder', { 
            name : data.text, 
            parent : data.node.parent,
        }, function( res ) {
            $.get('/s3mediamanager/default/get-bucket-object', function(data) {
                var obj = JSON.parse(data);
                $('#folderTree').jstree(true).settings.core.data = obj.folderObject;
                $('#folderTree').jstree(true).refresh();
                bucketObject = JSON.parse(obj.bucketObject);
                $('#mm__wrapper').unblock();
            }); 
        });

    }).bind('delete_node.jstree', function(e, data) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            // @todo: only remove the node if ajax does not return an error rather than reloding the whole thing
            if (result.value === true) {

                $.post('/s3mediamanager/default/delete-folder', { 
                    key : data.node.id,
                }, function(data) {
                    var result = JSON.parse(data);
        
                    if ( result.error == 'folder not empty' )
                    {
                        Swal.fire({
                            title: 'Error!',
                            text: `This folder contains objects and therefore cannot be deleted. Please remove the objects 
                                before deleting the folder.`,
                            icon: 'error',
                        });
        
                        $.get('/s3mediamanager/default/get-bucket-object', function(data) {
                            var obj = JSON.parse(data);
                            $('#folderTree').jstree(true).settings.core.data = obj.folderObject;
                            $('#folderTree').jstree(true).refresh();
                            bucketObject = JSON.parse(obj.bucketObject);
                        });     
                    } else {
                        Swal.fire(
                            'Deleted!',
                            'Your file has been deleted.',
                            'success'
                        );                        
                    }
                });   
            } else {
                $.get('/s3mediamanager/default/get-bucket-object', function(data) {
                    var obj = JSON.parse(data);
                    $('#folderTree').jstree(true).settings.core.data = obj.folderObject;
                    $('#folderTree').jstree(true).refresh();
                    bucketObject = JSON.parse(obj.bucketObject);
                });                 
            }
          });        
    });
}


function blocker()
{
    $('#mm__wrapper').block({ 
        message : '<div class="loader"><span class="ball"></span><span class="ball2"></span><ul><li></li><li></li><li></li><li></li><li></li></ul></div>',
        css : { backgroundColor: 'none', border: 'none' }
    });    
}

/**
* DropZone
*/
var uploader = new Dropzone('#s3mm-file-upload-form', { 
    init: function() {
        this.on("success", function(file) {
            // Clear the dropzone and add the new file
            this.removeAllFiles();

            var key = `${$('#s3mm-upload-path').val()}/${file.name}`;

            // Add the new item to the existing bucketObject
            $.get('/s3mediamanager/default/get-object?justPath=false&key='+key, function(data) {
                if ( typeof(bucketObject[$('#s3mm-upload-path').val()]) == "undefined" )
                {
                    bucketObject[$('#s3mm-upload-path').val()] = new Array;
                }

                $.get('/s3mediamanager/default/get-bucket-object', function(data) {
                    var obj = JSON.parse(data);
                    $('#folderTree').jstree(true).settings.core.data = obj.folderObject;
                    $('#folderTree').jstree(true).refresh();
                    bucketObject = JSON.parse(obj.bucketObject);
                });     
            });
        });
    }
});
    
/**
 * Select a file
 */
$('#s3mm-object-list').on('click', '.fileRow', function() {
    blocker();
    $('#s3mm-object-list tr').removeClass('table-info');
    $(this).addClass('table-info');

    var key = $(this).find('.s3mm-object').attr('id');
    $.get('/s3mediamanager/default/get-object?key='+key, function(data) {
        var data = JSON.parse(data);
        $('#insertFile').prop('disabled', false);
        $('#selectedFile').val(data.effectiveUrl);
        $('#s3mm-file-url-display').html(data.effectiveUrl);
        $('#s3mm-copy-file-uri').removeClass('invisible');

        if ( $('#s3mm-copy-file-uri').hasClass('fa-thumbs-up') )
        {
            $('#s3mm-copy-file-uri').removeClass('fa-thumbs-up');
            $('#s3mm-copy-file-uri').addClass('fa-copy');
        }
        $('#mm__wrapper').unblock();
    });
});

/**
 * Copy a File URI
 */
$('#s3mm-copy-file-uri').click( function() {
    var el = document.getElementById('s3mm-file-url-display');
    var range = document.createRange();
    range.selectNodeContents(el);
    var sel = window.getSelection();
    sel.removeAllRanges();
    sel.addRange(range);
    document.execCommand('copy');
    $('#s3mm-copy-file-uri').removeClass('fa-copy');
    $('#s3mm-copy-file-uri').addClass('fa-thumbs-up');
});

/**
 * When a folder in the jstree is selected, get those files and redraw
 */
$('#folderTree').on("changed.jstree", function (e, data) {

    $('#s3mm-upload-path').val(data.selected);
    $('#s3mm-object-path-display').html(data.selected);
    $('#s3mm-file-url-display').html(null);
    $('#s3mm-copy-file-uri').addClass('invisible');

    $('#files').html(' ');

    for ( var file in bucketObject[data.selected] )
    {
        var filename = bucketObject[data.selected][file].text;
        var object = bucketObject[data.selected][file];
        var fileRow = buildFileRow(object.icon, filename, object.id, object.modified, convertSize(object.size));

       $('#files').append(fileRow);
    }
});

/**
 * Download an s3 object
 */
$('#s3mm-object-list').on('click', '.s3mm-object', function(e, data) {
    var key = $(this).attr('id');

    window.location.assign('/s3mediamanager/default/download?key='+key);
});

/**
 * delete an s3 object
 */
$('#s3mm-object-list').on('click', '.s3mm-delete-object', function(e, data) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.value === true) {
            Swal.fire(
                'Deleted!',
                'Your file has been deleted.',
                'success'
            );

            var key = $(this).attr('id');

            $.get('/s3mediamanager/default/delete?key='+key, function(data) {
                $.get('/s3mediamanager/default/get-bucket-object', function(data) {
                    var obj = JSON.parse(data);
                    $('#folderTree').jstree(true).settings.core.data = obj.folderObject;
                    $('#folderTree').jstree(true).refresh();
                    bucketObject = JSON.parse(obj.bucketObject);
                });
            });

            var parenttr = $(this).closest('tr');
            $(parenttr).remove();    
        }
      });
});

function convertSize(filesize)
{
  var size = filesize.split(' ');

  if ( size[1] === 'bytes' )
    var dim = 'B';

  if ( size[1] === 'kibibytes' )
    var dim = 'KB';

  if ( size[1] === 'mebibytes' )
    var dim = 'MB';

  return size[0]+' '+dim;
}

function buildFileRow(icon, filename, id, modified, size)
{
    var filerow = '<tr class="fileRow">'+
        '<td>'+
            '<a href="#" id="'+id+'" class="s3mm-object" data-toggle="tooltip" data-placement="top" title="Download"><i class="far fa-arrow-alt-circle-down text-info"></i></a> '+
            '<a href="#" id="'+id+'" class="s3mm-delete-object" data-toggle="tooltip" data-placement="top" title="Delete"><i class="far fa-times-circle text-danger"></i></a> '+
        '</td>'+   
        '<td><i class="'+icon+'"></i> '+filename+'</a></td>'+
        '<td>'+modified+'</td>'+
        '<td class="text-right text-muted">'+size+'</td>'+
    '</tr>';

    return filerow;
}

function humanFileSize(bytes, si) {
    var thresh = si ? 1000 : 1024;
    if(Math.abs(bytes) < thresh) {
        return bytes + ' B';
    }
    var units = si
        ? ['kB','MB','GB','TB','PB','EB','ZB','YB']
        : ['KB','MB','GB','TB','PB','EB','ZB','YB'];
    var u = -1;
    do {
        bytes /= thresh;
        ++u;
    } while(Math.abs(bytes) >= thresh && u < units.length - 1);
    return bytes.toFixed(1)+' '+units[u];
}

function filemanagerTinyMCE(callback, value, meta)
{
    $('#MediaManager').modal('show');
}