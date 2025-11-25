<h1 class="h4 mb-3">Tạo bài viết</h1>
<form method="post" action="<?= htmlspecialchars($baseUrl) ?>/admin/articles/store" enctype="multipart/form-data">
    <div class="mb-3">
        <label class="form-label">Tiêu đề</label>
        <input type="text" name="title" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Tóm tắt</label>
        <textarea name="summary" class="form-control" rows="3"></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Nội dung</label>
        <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
            <span class="text-muted small">Markdown nhanh:</span>
            <div class="btn-group btn-group-sm" role="group" aria-label="Markdown helpers">
                <button class="btn btn-outline-light" type="button" data-md-target="content" data-snippet="# Tieu de lon\n\n">H1</button>
                <button class="btn btn-outline-light" type="button" data-md-target="content" data-snippet="## Muc nho\n\n">H2</button>
                <button class="btn btn-outline-light" type="button" data-md-target="content" data-snippet="- Item 1\n- Item 2\n\n">List</button>
                <button class="btn btn-outline-light" type="button" data-md-target="content" data-snippet="![caption](duong_dan_anh)\n\n">Anh</button>
                <button class="btn btn-outline-light" type="button" data-md-target="content" data-snippet="@[youtube](https://youtu.be/VIDEO_ID)\n\n">YouTube</button>
                <button class="btn btn-outline-light" type="button" data-md-target="content" data-snippet="@[vimeo](https://vimeo.com/123456789)\n\n">Vimeo</button>
                <button class="btn btn-outline-light" type="button" data-md-target="content" data-snippet="@[video](uploads/video.mp4)\n\n">Video</button>
            </div>
        </div>
        <textarea name="content" class="form-control" rows="6"></textarea>
        <div class="form-text">Ho tro Markdown: # tieu de, ## muc, ![caption](duong_dan_anh), @[youtube](link), @[vimeo](link), @[video](link mp4)</div>
    </div>
    <div class="mb-3">
        <label class="form-label">Danh mục</label>
        <select name="category_id" class="form-select">
            <?php foreach ($categories as $c): ?>
                <option value="<?= (int)$c['category_id'] ?>"><?= htmlspecialchars($c['category_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Hình ảnh</label>
        <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
        <div id="image-options" class="row g-3 mt-2"></div>
    </div>
    <button class="btn btn-primary">Lưu</button>
</form>

<script>
// Build per-image options (size, align, caption) for newly selected files
(function(){
  const input = document.querySelector('input[name="images[]"]');
  const wrap = document.getElementById('image-options');
  if (!input || !wrap) return;
  input.addEventListener('change', () => {
    wrap.innerHTML = '';
    const files = Array.from(input.files || []);
    files.forEach((file, idx) => {
      const id = 'imgopt_' + idx;
      const col = document.createElement('div');
      col.className = 'col-12';
      col.innerHTML = `
        <div class="card p-2">
          <div class="d-flex align-items-start gap-3">
            <img id="${id}" class="rounded" style="width:140px;height:90px;object-fit:cover" alt="Xem trước">
            <div class="flex-grow-1 row g-2">
              <div class="col-md-4">
                <label class="form-label small">Kích thước</label>
                <select name="image_size[]" class="form-select form-select-sm">
                  <option value="img-small">Nhỏ</option>
                  <option value="img-medium" selected>Trung bình</option>
                  <option value="img-large">Lớn</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label small">Căn chỉnh</label>
                <select name="image_align[]" class="form-select form-select-sm">
                  <option value="img-left">Trái</option>
                  <option value="img-center" selected>Giữa</option>
                  <option value="img-right">Phải</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label small">Chú thích</label>
                <input name="image_caption[]" class="form-control form-control-sm" placeholder="Mô tả ngắn cho ảnh">
              </div>
            </div>
          </div>
        </div>`;
      wrap.appendChild(col);
      const img = document.getElementById(id);
      const reader = new FileReader();
      reader.onload = e => { img.src = e.target.result; };
      reader.readAsDataURL(file);
    });
  });
})();

// Markdown helper buttons (insert snippet at cursor)
(function(){
  const textarea = document.querySelector('textarea[name="content"]');
  const buttons = document.querySelectorAll('[data-md-target="content"]');
  if (!textarea || buttons.length === 0) return;

  function insert(snippet){
    const start = textarea.selectionStart ?? textarea.value.length;
    const end   = textarea.selectionEnd ?? textarea.value.length;
    const before = textarea.value.slice(0, start);
    const after  = textarea.value.slice(end);
    textarea.value = before + snippet + after;
    const pos = start + snippet.length;
    textarea.setSelectionRange(pos, pos);
    textarea.focus();
  }

  buttons.forEach(btn => {
    btn.addEventListener('click', () => {
      const snip = (btn.getAttribute('data-snippet') || '').replace(/\\n/g, '\n').replace(/\\t/g, '\t');
      if (snip) insert(snip);
    });
  });
})();
</script>
