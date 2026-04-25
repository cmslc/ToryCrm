<?php
/**
 * Activity Exchange Plugin - Partial View
 *
 * Variables required:
 *   $entityType  - 'contact', 'quotation', 'order', 'contract', 'deal'
 *   $entityId    - ID of the entity
 *   $activities  - Array of activities (with replies loaded)
 *   $allUsers    - Array of all active users (for @mention)
 */
$_entityType = $entityType ?? 'contact';
$_entityId = $entityId ?? 0;
$_activities = $activities ?? [];
$_allUsers = $allUsers ?? [];
$_userAvatars = [];
foreach ($_allUsers as $u) { $_userAvatars[$u['name']] = $u['avatar'] ?? null; }
$_fieldName = $_entityType . '_id'; // contact_id, quotation_id, etc.
?>

<?php if (empty($_noCard)): ?>
<div class="card" id="activity-exchange-card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-chat-3-line me-1"></i> Trao đổi</h5>
    </div>
    <div class="card-body">
<?php endif; ?>
<div id="activity-exchange-inner">
        <!-- Compose Area -->
        <form method="POST" action="<?= url('activities/store') ?>" enctype="multipart/form-data" id="composeForm">
            <?= csrf_field() ?>
            <input type="hidden" name="<?= $_fieldName ?>" value="<?= $_entityId ?>">
            <input type="hidden" name="type" value="note" id="activityType">
            <input type="hidden" name="tagged_users" id="taggedUsers" value="">
            <input type="hidden" name="latitude" id="checkinLat" value="">
            <input type="hidden" name="longitude" id="checkinLng" value="">
            <input type="hidden" name="address" id="checkinAddress" value="">

            <div class="border rounded mb-3">
                <textarea name="title" class="form-control border-0" rows="4" placeholder="Nhập nội dung trao đổi, ghi chú..." required id="activityTextarea" style="resize:none"></textarea>
                <div class="d-flex align-items-center justify-content-between px-3 py-2 border-top bg-light" style="border-radius:0 0 6px 6px">
                    <div class="d-flex gap-3">
                        <label class="text-muted" style="cursor:pointer" title="Đính kèm file">
                            <i class="ri-attachment-2 fs-18"></i>
                            <input type="file" name="attachments[]" class="d-none" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip,.rar,.dwg,.dxf,.cad,.dwf,.skp,.3ds,.obj,.stl,.step,.stp,.iges,.igs" multiple onchange="previewAttach(this)">
                        </label>
                        <span class="text-muted" style="cursor:pointer" title="Tag @người dùng" onclick="var ta=document.getElementById('activityTextarea');ta.value+=' @';ta.focus();ta.dispatchEvent(new Event('input'));">
                            <i class="ri-at-line fs-18"></i>
                        </span>
                        <span class="text-muted" style="cursor:pointer" title="Emoji" onclick="var ta=document.getElementById('activityTextarea');ta.value+=' 😊';ta.focus();">
                            <i class="ri-emotion-happy-line fs-18"></i>
                        </span>
                        <span class="text-muted" style="cursor:pointer" title="Check-in vị trí" id="checkinBtn">
                            <i class="ri-map-pin-line fs-18"></i>
                        </span>
                    </div>
                    <button type="submit" class="btn btn-primary px-4">Gửi</button>
                </div>
                <div id="attachBadge" style="display:none" class="px-3 py-2 border-top bg-light"></div>
                <div id="checkinBadge" style="display:none" class="px-3 py-2 border-top bg-success-subtle fs-13">
                    <div class="d-flex align-items-start gap-2">
                        <i class="ri-map-pin-fill text-success mt-1"></i>
                        <div class="flex-grow-1">
                            <input type="text" id="checkinAddressInput" class="form-control form-control border-0 bg-transparent p-0 text-success fw-medium" placeholder="Địa chỉ..." style="box-shadow:none">
                            <div class="d-flex align-items-center gap-2 text-muted" style="font-size:11px">
                                <span id="checkinCoords"></span>
                                <span id="checkinAccuracy" class="badge bg-warning-subtle text-warning" style="font-size:10px"></span>
                                <a href="#" id="checkinMapPreview" target="_blank" class="text-primary text-decoration-none">Xem bản đồ</a>
                            </div>
                        </div>
                        <i class="ri-close-line text-muted" style="cursor:pointer" onclick="clearCheckin()"></i>
                    </div>
                </div>
            </div>
        </form>

        <!-- Activity Feed -->
        <div id="activityFeed" style="max-height:600px;overflow-y:auto">
            <?php if (!empty($_activities)): ?>
                <?php foreach ($_activities as $act):
                    $userName = $act['user_name'] ?? 'Hệ thống';
                    $userAvatar = $_userAvatars[$userName] ?? null;
                    $initial = mb_substr($userName, 0, 1);
                    $isSystem = in_array($act['type'], ['system','deal']);
                    $content = e($act['title']);
                    $content = preg_replace('/@([^\s,\.]+(?:\s[^\s,\.@]+){0,4})/', '<span class="text-primary fw-medium">@$1</span>', $content);
                    $content = preg_replace('/(https?:\/\/\S+)/', '<a href="$1" target="_blank" class="text-primary">$1</a>', $content);
                ?>
                <div class="d-flex gap-3 py-3 <?= $isSystem ? 'bg-light rounded px-3' : '' ?>" style="border-bottom:1px solid #f3f3f3">
                    <div class="flex-shrink-0">
                        <?php if ($userAvatar): ?>
                        <img src="<?= asset($userAvatar) ?>" class="rounded-circle" width="40" height="40" style="object-fit:cover">
                        <?php else: ?>
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:40px;height:40px;font-size:14px"><?= strtoupper($initial) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <strong style="font-size:14px"><?= e($userName) ?></strong>
                            <small class="text-muted"><?= !empty($act['created_at']) ? date('d/m/Y H:i', strtotime($act['created_at'])) : '' ?></small>
                        </div>
                        <div class="act-content" data-id="<?= $act['id'] ?>" style="white-space:pre-wrap;word-break:break-word"><?= $content ?></div>
                        <div class="act-edit d-none" data-id="<?= $act['id'] ?>" data-original="<?= e($act['title']) ?>">
                            <textarea class="form-control" rows="3"><?= e($act['title']) ?></textarea>
                            <div class="d-flex gap-2 mt-2">
                                <button type="button" class="btn btn-sm btn-primary" onclick="saveEdit(<?= $act['id'] ?>)">Lưu</button>
                                <button type="button" class="btn btn-sm btn-soft-secondary" onclick="cancelEdit(<?= $act['id'] ?>)">Hủy</button>
                            </div>
                        </div>
                        <?php if (!empty($act['description'])): ?>
                        <div class="text-muted mt-1" style="font-size:13px;white-space:pre-wrap"><?= e($act['description']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($act['latitude']) && !empty($act['longitude'])): ?>
                        <a href="https://www.google.com/maps?q=<?= $act['latitude'] ?>,<?= $act['longitude'] ?>" target="_blank" class="d-inline-flex align-items-center gap-1 mt-2 px-2 py-1 bg-success-subtle text-success rounded text-decoration-none" style="font-size:12px">
                            <i class="ri-map-pin-fill"></i><?= e($act['address'] ?? ($act['latitude'] . ', ' . $act['longitude'])) ?>
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($act['attachment'])):
                            $attPaths = explode('|', $act['attachment']);
                            $attNames = explode('|', $act['attachment_name'] ?? '');
                            $fileIcons = ['pdf'=>'ri-file-pdf-line text-danger','doc'=>'ri-file-word-line text-primary','docx'=>'ri-file-word-line text-primary','xls'=>'ri-file-excel-line text-success','xlsx'=>'ri-file-excel-line text-success','dwg'=>'ri-draft-line text-dark','dxf'=>'ri-draft-line text-dark','cad'=>'ri-draft-line text-dark'];
                        ?>
                        <div class="mt-2 d-flex flex-wrap gap-2">
                            <?php foreach ($attPaths as $ai => $aPath):
                                $aPath = trim($aPath); if (!$aPath) continue;
                                $aExt = strtolower(pathinfo($aPath, PATHINFO_EXTENSION));
                                $isImage = in_array($aExt, ['jpg','jpeg','png','gif','webp']);
                                $aName = trim($attNames[$ai] ?? basename($aPath));
                            ?>
                                <?php if ($isImage): ?>
                                <a href="<?= asset($aPath) ?>" target="_blank"><img src="<?= asset($aPath) ?>" class="rounded border" style="max-width:200px;max-height:150px;object-fit:cover"></a>
                                <?php else: ?>
                                <a href="<?= asset($aPath) ?>" target="_blank" class="d-flex align-items-center gap-2 p-2 bg-light rounded text-decoration-none">
                                    <i class="<?= $fileIcons[$aExt] ?? 'ri-file-line text-muted' ?> fs-20"></i>
                                    <span class="text-dark" style="font-size:13px"><?= e($aName) ?></span>
                                    <i class="ri-download-line text-muted"></i>
                                </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Like / Dislike / Reply -->
                        <div class="d-flex align-items-center gap-3 mt-2" style="font-size:13px">
                            <span class="act-btn <?= ($act['my_reaction'] ?? '') === 'like' ? 'text-primary fw-medium' : 'text-muted' ?>" style="cursor:pointer" onclick="reactActivity(<?= $act['id'] ?>,'like',this)">
                                <i class="ri-thumb-up-<?= ($act['my_reaction'] ?? '') === 'like' ? 'fill' : 'line' ?>"></i><?php if (($act['likes'] ?? 0) > 0): ?> <span class="react-count"><?= $act['likes'] ?></span><?php endif; ?>
                            </span>
                            <span class="act-btn <?= ($act['my_reaction'] ?? '') === 'dislike' ? 'text-danger fw-medium' : 'text-muted' ?>" style="cursor:pointer" onclick="reactActivity(<?= $act['id'] ?>,'dislike',this)">
                                <i class="ri-thumb-down-<?= ($act['my_reaction'] ?? '') === 'dislike' ? 'fill' : 'line' ?>"></i><?php if (($act['dislikes'] ?? 0) > 0): ?> <span class="react-count"><?= $act['dislikes'] ?></span><?php endif; ?>
                            </span>
                            <span class="text-muted act-btn" style="cursor:pointer" onclick="toggleReplyBox(<?= $act['id'] ?>)">
                                <i class="ri-reply-line"></i> Trả lời
                            </span>
                            <?php if (($act['user_id'] ?? 0) == ($_SESSION['user']['id'] ?? 0)): ?>
                            <span class="text-muted act-btn" style="cursor:pointer" onclick="startEdit(<?= $act['id'] ?>)">
                                <i class="ri-pencil-line"></i> Sửa
                            </span>
                            <?php endif; ?>
                        </div>

                        <!-- Replies -->
                        <?php if (!empty($act['replies'])): ?>
                        <div class="ms-4 mt-2 border-start ps-3">
                            <?php foreach ($act['replies'] as $reply):
                                $rAvatar = $reply['user_avatar'] ?? null;
                                $rName = $reply['user_name'] ?? 'Hệ thống';
                                $rContent = e($reply['title']);
                                $rContent = preg_replace('/@([^\s,\.]+(?:\s[^\s,\.@]+){0,4})/', '<span class="text-primary fw-medium">@$1</span>', $rContent);
                            ?>
                            <div class="d-flex gap-2 py-2" style="border-bottom:1px solid #f8f8f8">
                                <?php if ($rAvatar): ?>
                                <img src="<?= asset($rAvatar) ?>" class="rounded-circle" width="28" height="28" style="object-fit:cover">
                                <?php else: ?>
                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width:28px;height:28px;font-size:11px"><?= mb_substr($rName, 0, 1) ?></div>
                                <?php endif; ?>
                                <div class="flex-grow-1">
                                    <strong style="font-size:13px"><?= e($rName) ?></strong>
                                    <small class="text-muted ms-1"><?= date('d/m H:i', strtotime($reply['created_at'])) ?></small>
                                    <div class="act-content" data-id="<?= $reply['id'] ?>" style="font-size:13px"><?= $rContent ?></div>
                                    <div class="act-edit d-none" data-id="<?= $reply['id'] ?>" data-original="<?= e($reply['title']) ?>">
                                        <textarea class="form-control form-control-sm" rows="2"><?= e($reply['title']) ?></textarea>
                                        <div class="d-flex gap-2 mt-1">
                                            <button type="button" class="btn btn-sm btn-primary" onclick="saveEdit(<?= $reply['id'] ?>)">Lưu</button>
                                            <button type="button" class="btn btn-sm btn-soft-secondary" onclick="cancelEdit(<?= $reply['id'] ?>)">Hủy</button>
                                        </div>
                                    </div>
                                    <?php if (!empty($reply['attachment'])):
                                        $rPath = trim($reply['attachment']);
                                        $rExt = strtolower(pathinfo($rPath, PATHINFO_EXTENSION));
                                        $rIsImg = in_array($rExt, ['jpg','jpeg','png','gif','webp']);
                                    ?>
                                    <div class="mt-1">
                                        <?php if ($rIsImg): ?><a href="<?= asset($rPath) ?>" target="_blank"><img src="<?= asset($rPath) ?>" class="rounded border" style="max-width:200px;max-height:120px"></a>
                                        <?php else: ?><a href="<?= asset($rPath) ?>" target="_blank" class="text-primary" style="font-size:12px"><i class="ri-file-line me-1"></i><?= e($reply['attachment_name'] ?? basename($rPath)) ?></a><?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                    <div class="d-flex align-items-center gap-3 mt-1" style="font-size:12px">
                                        <span class="act-btn <?= ($reply['my_reaction'] ?? '') === 'like' ? 'text-primary fw-medium' : 'text-muted' ?>" style="cursor:pointer" onclick="reactActivity(<?= $reply['id'] ?>,'like',this)">
                                            <i class="ri-thumb-up-<?= ($reply['my_reaction'] ?? '') === 'like' ? 'fill' : 'line' ?>"></i><?php if (($reply['likes'] ?? 0) > 0): ?> <?= $reply['likes'] ?><?php endif; ?>
                                        </span>
                                        <span class="act-btn <?= ($reply['my_reaction'] ?? '') === 'dislike' ? 'text-danger fw-medium' : 'text-muted' ?>" style="cursor:pointer" onclick="reactActivity(<?= $reply['id'] ?>,'dislike',this)">
                                            <i class="ri-thumb-down-<?= ($reply['my_reaction'] ?? '') === 'dislike' ? 'fill' : 'line' ?>"></i><?php if (($reply['dislikes'] ?? 0) > 0): ?> <?= $reply['dislikes'] ?><?php endif; ?>
                                        </span>
                                        <span class="text-muted act-btn" style="cursor:pointer" onclick="toggleReplyBox(<?= $act['id'] ?>)"><i class="ri-reply-line"></i> Trả lời</span>
                                        <?php if (($reply['user_id'] ?? 0) == ($_SESSION['user']['id'] ?? 0)): ?>
                                        <span class="text-muted act-btn" style="cursor:pointer" onclick="startEdit(<?= $reply['id'] ?>)"><i class="ri-pencil-line"></i> Sửa</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Reply Box -->
                        <div class="ms-4 mt-2 d-none" id="replyBox-<?= $act['id'] ?>">
                            <div class="d-flex gap-2 align-items-center">
                                <input type="text" class="form-control" placeholder="Viết trả lời..." id="replyInput-<?= $act['id'] ?>" onkeydown="if(event.key==='Enter'){event.preventDefault();submitReply(<?= $act['id'] ?>)}">
                                <label class="btn btn-soft-secondary mb-0" title="Đính kèm file" style="padding:6px 10px">
                                    <i class="ri-attachment-2"></i>
                                    <input type="file" class="d-none" id="replyFile-<?= $act['id'] ?>" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.dwg,.dxf,.cad,.zip,.rar">
                                </label>
                                <button class="btn btn-primary" onclick="submitReply(<?= $act['id'] ?>)" style="padding:6px 16px">Gửi</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="ri-chat-3-line fs-48 text-muted"></i>
                    <p class="text-muted mt-2">Chưa có hoạt động trao đổi</p>
                </div>
            <?php endif; ?>
        </div>
</div>
<?php if (empty($_noCard)): ?>
    </div>
</div>
<?php endif; ?>

<script>
// Compose form stays on top (above feed) — no DOM reorder.

// Attachment preview
function previewAttach(input) {
    var badge = document.getElementById('attachBadge');
    if (!input.files || !input.files.length) { badge.style.display = 'none'; return; }
    var icons = {pdf:'ri-file-pdf-line text-danger',doc:'ri-file-word-line text-primary',docx:'ri-file-word-line text-primary',xls:'ri-file-excel-line text-success',xlsx:'ri-file-excel-line text-success',dwg:'ri-draft-line text-dark'};
    var html = '<div class="d-flex flex-wrap gap-2">';
    Array.from(input.files).forEach(function(file, i) {
        var size = file.size > 1048576 ? (file.size/1048576).toFixed(1)+'MB' : Math.round(file.size/1024)+'KB';
        var ext = file.name.split('.').pop().toLowerCase();
        var isImg = file.type.startsWith('image/');
        var xBtn = '<span class="position-absolute top-0 end-0 bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width:18px;height:18px;cursor:pointer;font-size:10px;transform:translate(5px,-5px)" onclick="removeAttachFile('+i+')"><i class="ri-close-line"></i></span>';
        if (isImg) { html += '<div class="position-relative" style="width:80px;height:80px;margin:5px"><img src="" class="attach-thumb rounded border" data-idx="'+i+'" style="width:100%;height:100%;object-fit:cover">'+xBtn+'</div>'; }
        else { html += '<div class="border rounded p-2 d-flex align-items-center gap-2 position-relative" style="max-width:180px"><i class="'+(icons[ext]||'ri-file-line text-muted')+' fs-20"></i><div style="min-width:0"><div class="text-truncate" style="font-size:12px;max-width:120px">'+file.name+'</div><small class="text-muted">'+size+'</small></div>'+xBtn+'</div>'; }
    });
    html += '</div>';
    badge.innerHTML = html; badge.style.display = 'block';
    Array.from(input.files).forEach(function(file, i) {
        if (!file.type.startsWith('image/')) return;
        var reader = new FileReader();
        reader.onload = function(e) { var img = badge.querySelector('.attach-thumb[data-idx="'+i+'"]'); if (img) img.src = e.target.result; };
        reader.readAsDataURL(file);
    });
}
function clearAttach() { var input = document.querySelector('#composeForm input[name="attachments[]"]'); if(input)input.value=''; document.getElementById('attachBadge').style.display='none'; }
function removeAttachFile(idx) { var input=document.querySelector('#composeForm input[name="attachments[]"]');var dt=new DataTransfer();var files=input._filteredFiles||input.files;for(var i=0;i<files.length;i++){if(i!==idx)dt.items.add(files[i]);}input.files=dt.files;input._filteredFiles=dt.files;if(!dt.files.length)clearAttach();else previewAttach(input); }

// React (inline update)
function reactActivity(id, type, el) {
    fetch('<?= url("activities") ?>/'+id+'/react', { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'<?= csrf_token() ?>'}, body:JSON.stringify({type:type}) })
    .then(r=>r.json()).then(function(data) {
        if (!data.likes && data.likes !== 0) return;
        var row = el.closest('.d-flex.align-items-center.gap-3');
        var btns = row.querySelectorAll('.act-btn');
        btns[0].className = 'act-btn '+(data.my==='like'?'text-primary fw-medium':'text-muted');
        btns[0].innerHTML = '<i class="ri-thumb-up-'+(data.my==='like'?'fill':'line')+'"></i>'+(data.likes>0?' <span class="react-count">'+data.likes+'</span>':'');
        btns[1].className = 'act-btn '+(data.my==='dislike'?'text-danger fw-medium':'text-muted');
        btns[1].innerHTML = '<i class="ri-thumb-down-'+(data.my==='dislike'?'fill':'line')+'"></i>'+(data.dislikes>0?' <span class="react-count">'+data.dislikes+'</span>':'');
    });
}

// Highlight @mentions in JS
function highlightMentions(text) {
    return text.replace(/@([^\s,\.]+(?:\s[^\s,\.@]+){0,4})/g, '<span class="text-primary fw-medium">@$1</span>');
}

// Reply
function toggleReplyBox(id) { var box=document.getElementById('replyBox-'+id); box.classList.toggle('d-none'); if(!box.classList.contains('d-none'))document.getElementById('replyInput-'+id).focus(); }
function submitReply(id) {
    var input=document.getElementById('replyInput-'+id); var fileInput=document.getElementById('replyFile-'+id);
    var content=input.value.trim(); if(!content&&(!fileInput||!fileInput.files[0]))return;
    var fd=new FormData(); fd.append('content',content); fd.append('_token','<?= csrf_token() ?>');
    if(fileInput&&fileInput.files[0])fd.append('attachment',fileInput.files[0]);
    fetch('<?= url("activities") ?>/'+id+'/reply',{method:'POST',headers:{'X-CSRF-TOKEN':'<?= csrf_token() ?>'},body:fd})
    .then(r=>r.json()).then(function(data){
        if(!data.success)return;
        var r=data.reply;var initial=(r.user_name||'?').charAt(0).toUpperCase();
        var avatar=r.user_avatar?'<img src="/'+r.user_avatar+'" class="rounded-circle" width="28" height="28" style="object-fit:cover">':'<div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width:28px;height:28px;font-size:11px">'+initial+'</div>';
        var actions='<div class="d-flex align-items-center gap-3 mt-1" style="font-size:12px"><span class="act-btn text-muted" style="cursor:pointer" onclick="reactActivity('+r.id+',\'like\',this)"><i class="ri-thumb-up-line"></i></span><span class="act-btn text-muted" style="cursor:pointer" onclick="reactActivity('+r.id+',\'dislike\',this)"><i class="ri-thumb-down-line"></i></span><span class="text-muted act-btn" style="cursor:pointer" onclick="toggleReplyBox('+id+')"><i class="ri-reply-line"></i> Trả lời</span></div>';
        var attachHtml='';
        if(r.attachment){var aExt=r.attachment.split('.').pop().toLowerCase();var isImg=['jpg','jpeg','png','gif','webp'].indexOf(aExt)!==-1;if(isImg){attachHtml='<div class="mt-1"><a href="/'+r.attachment+'" target="_blank"><img src="/'+r.attachment+'" class="rounded border" style="max-width:200px;max-height:120px"></a></div>';}else{attachHtml='<div class="mt-1"><a href="/'+r.attachment+'" target="_blank" class="text-primary" style="font-size:12px"><i class="ri-file-line me-1"></i>'+(r.attachment_name||r.attachment.split('/').pop())+'</a></div>';}}
        var html='<div class="d-flex gap-2 py-2" style="border-bottom:1px solid #f8f8f8">'+avatar+'<div class="flex-grow-1"><strong style="font-size:13px">'+r.user_name+'</strong> <small class="text-muted">vừa xong</small><div style="font-size:13px">'+highlightMentions(r.title||'')+'</div>'+attachHtml+actions+'</div></div>';
        var box=document.getElementById('replyBox-'+id);
        if(fileInput)fileInput.value='';
        var repliesDiv=box.previousElementSibling;
        if(!repliesDiv||!repliesDiv.classList.contains('border-start')){var newDiv=document.createElement('div');newDiv.className='ms-4 mt-2 border-start ps-3';box.parentNode.insertBefore(newDiv,box);repliesDiv=newDiv;}
        repliesDiv.insertAdjacentHTML('beforeend',html);
        input.value='';
    });
}

// @mention autocomplete
(function(){
    var users=<?= json_encode(array_map(function($u){return['id'=>$u['id'],'name'=>$u['name'],'avatar'=>$u['avatar']??null];}, $_allUsers)) ?>;
    var dd=document.createElement('div');dd.className='border rounded bg-white shadow';dd.style.cssText='position:fixed;z-index:1070;display:none;max-height:250px;overflow-y:auto;width:280px';document.body.appendChild(dd);
    var activeInput=null;
    function updatePos(){
        if(!activeInput||dd.style.display==='none')return;
        var rect=activeInput.getBoundingClientRect();
        var spaceBelow=window.innerHeight-rect.bottom;
        if(spaceBelow>200){dd.style.top=(rect.bottom+4)+'px';dd.style.bottom='auto';}
        else{dd.style.bottom=(window.innerHeight-rect.top+4)+'px';dd.style.top='auto';}
        dd.style.left=Math.max(rect.left,10)+'px';
    }
    window.addEventListener('scroll',updatePos,true);
    window.pickMention=function(name,id){
        if(!activeInput)return;var val=activeInput.value;var pos=activeInput.selectionStart;var before=val.substring(0,pos);var after=val.substring(pos);
        var atIdx=before.lastIndexOf('@');if(atIdx!==-1){activeInput.value=before.substring(0,atIdx)+'@'+name+' '+after;activeInput.selectionStart=activeInput.selectionEnd=atIdx+name.length+2;}
        var hidden=document.getElementById('taggedUsers');if(hidden){var ids=hidden.value?hidden.value.split(','):[];if(ids.indexOf(String(id))===-1)ids.push(id);hidden.value=ids.join(',');}
        dd.style.display='none';activeInput.focus();
    };
    document.addEventListener('input',function(e){
        if(e.target.tagName!=='TEXTAREA'&&e.target.tagName!=='INPUT')return;
        var val=e.target.value;var pos=e.target.selectionStart;var before=val.substring(0,pos);var match=before.match(/@([^\s@]*)$/);
        if(match){
            activeInput=e.target;var q=(match[1]||'').toLowerCase();
            var filtered=users.filter(function(u){return!q||u.name.toLowerCase().indexOf(q)!==-1;}).slice(0,10);
            if(!filtered.length){dd.style.display='none';return;}
            dd.innerHTML=filtered.map(function(u){
                var av=u.avatar?'<img src="/'+u.avatar+'" class="rounded-circle me-2" width="24" height="24" style="object-fit:cover">':'<div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width:24px;height:24px;font-size:10px">'+u.name.charAt(0).toUpperCase()+'</div>';
                return'<div class="d-flex align-items-center px-3 py-2" style="cursor:pointer;font-size:13px" onmousedown="pickMention(\''+u.name.replace(/'/g,"\\'")+'\','+u.id+')">'+av+'<span>'+u.name+'</span></div>';
            }).join('');
            updatePos();dd.style.display='block';
        }else{dd.style.display='none';}
    });
    document.addEventListener('keydown',function(e){if(e.key==='Escape')dd.style.display='none';});
    document.addEventListener('click',function(e){if(!dd.contains(e.target))dd.style.display='none';});
})();

// Enter to send (Shift+Enter for new line)
document.getElementById('activityTextarea')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        this.closest('form').submit();
    }
});

// Check-in: capture GPS + reverse geocode
window.clearCheckin = function() {
    document.getElementById('checkinLat').value = '';
    document.getElementById('checkinLng').value = '';
    document.getElementById('checkinAddress').value = '';
    document.getElementById('checkinAddressInput').value = '';
    document.getElementById('checkinBadge').style.display = 'none';
    var btn = document.getElementById('checkinBtn');
    if (btn) { btn.classList.remove('text-primary'); btn.innerHTML = '<i class="ri-map-pin-line fs-18"></i>'; }
};

// Sync editable address input back to hidden field
document.getElementById('checkinAddressInput')?.addEventListener('input', function() {
    document.getElementById('checkinAddress').value = this.value;
});

document.getElementById('checkinBtn')?.addEventListener('click', function() {
    if (!navigator.geolocation) { alert('Trình duyệt không hỗ trợ định vị GPS.'); return; }
    var btn = this;
    btn.classList.add('text-primary');
    btn.innerHTML = '<i class="ri-loader-4-line ri-spin fs-18"></i>';
    var ta = document.getElementById('activityTextarea');
    if (ta && !ta.value.trim()) ta.value = 'Check in';
    navigator.geolocation.getCurrentPosition(function(pos) {
        var lat = pos.coords.latitude.toFixed(7);
        var lng = pos.coords.longitude.toFixed(7);
        var acc = Math.round(pos.coords.accuracy || 0);
        document.getElementById('checkinLat').value = lat;
        document.getElementById('checkinLng').value = lng;
        btn.innerHTML = '<i class="ri-map-pin-fill fs-18"></i>';

        document.getElementById('checkinBadge').style.display = 'block';
        document.getElementById('checkinCoords').textContent = lat + ', ' + lng;
        var accEl = document.getElementById('checkinAccuracy');
        if (acc > 0) {
            accEl.textContent = '±' + (acc < 1000 ? acc + 'm' : (acc/1000).toFixed(1) + 'km');
            accEl.className = 'badge ' + (acc < 50 ? 'bg-success-subtle text-success' : acc < 500 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger') + '';
            accEl.style.fontSize = '10px';
        } else { accEl.textContent = ''; }
        document.getElementById('checkinMapPreview').href = 'https://www.google.com/maps?q=' + lat + ',' + lng;
        var addrInput = document.getElementById('checkinAddressInput');
        addrInput.value = 'Đang lấy địa chỉ...';
        addrInput.disabled = true;

        fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng + '&accept-language=vi&zoom=18')
            .then(r => r.json())
            .then(d => {
                var addr = d.display_name || '';
                addrInput.value = addr;
                addrInput.disabled = false;
                document.getElementById('checkinAddress').value = addr;
                addrInput.focus();
                addrInput.select();
            })
            .catch(() => { addrInput.value = ''; addrInput.disabled = false; addrInput.focus(); });
    }, function(err) {
        btn.classList.remove('text-primary');
        btn.innerHTML = '<i class="ri-map-pin-line fs-18"></i>';
        alert('Không lấy được vị trí: ' + (err.message || 'lỗi không rõ'));
    }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
});

// Feed shows newest at top, no autoscroll needed.

// Edit comment / reply
function startEdit(id) {
    var content = document.querySelector('.act-content[data-id="'+id+'"]');
    var editBox = document.querySelector('.act-edit[data-id="'+id+'"]');
    if (content && editBox) {
        content.classList.add('d-none');
        editBox.classList.remove('d-none');
        var ta = editBox.querySelector('textarea');
        if (ta) { ta.focus(); ta.setSelectionRange(ta.value.length, ta.value.length); }
    }
}
function cancelEdit(id) {
    var content = document.querySelector('.act-content[data-id="'+id+'"]');
    var editBox = document.querySelector('.act-edit[data-id="'+id+'"]');
    if (content && editBox) {
        var ta = editBox.querySelector('textarea');
        if (ta) ta.value = editBox.dataset.original || '';
        editBox.classList.add('d-none');
        content.classList.remove('d-none');
    }
}
function saveEdit(id) {
    var editBox = document.querySelector('.act-edit[data-id="'+id+'"]');
    if (!editBox) return;
    var ta = editBox.querySelector('textarea');
    var newTitle = (ta.value || '').trim();
    if (!newTitle) { alert('Nội dung không được để trống'); return; }
    var fd = new FormData();
    fd.append('title', newTitle);
    fetch('<?= url("activities") ?>/'+id+'/update', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '<?= csrf_token() ?>', 'X-Requested-With': 'XMLHttpRequest' },
        body: fd
    }).then(r => r.json()).then(function(data) {
        if (data.success) {
            var content = document.querySelector('.act-content[data-id="'+id+'"]');
            if (content) {
                var html = newTitle.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
                html = html.replace(/@([^\s,\.]+(?:\s[^\s,\.@]+){0,4})/g, '<span class="text-primary fw-medium">@$1</span>');
                html = html.replace(/(https?:\/\/\S+)/g, '<a href="$1" target="_blank" class="text-primary">$1</a>');
                content.innerHTML = html;
                content.classList.remove('d-none');
            }
            editBox.classList.add('d-none');
            editBox.dataset.original = newTitle;
        } else {
            alert(data.error || 'Lỗi cập nhật');
        }
    }).catch(function() { alert('Lỗi kết nối'); });
}
</script>
