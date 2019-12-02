<div class="modal fade clearfix" id="MediaManager" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-full">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title">Media Manager</h4>
            </div>
            <div class="modal-body"> 
                <?= $this->render('index', []) ?>      
            </div>
            <div class="modal-footer">
                <input type="hidden" id="selectedFile">
                <button type="button" class="btn btn-success" id="insertFile" disabled="true">
                    <i class="fas fa-arrow-circle-right"></i> Insert File
                </button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>