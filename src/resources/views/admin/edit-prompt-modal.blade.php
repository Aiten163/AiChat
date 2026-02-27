<div id="editForm" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border: 1px solid #ccc; z-index: 1000; box-shadow: 0 0 10px rgba(0,0,0,0.3);">
    <form action="{{ route('platform.base-prompts.update') }}" method="POST">
        @csrf
        <input type="hidden" name="id" id="editPromptId">

        <h5>Редактировать промт</h5>

        <div class="mb-3">
            <label class="form-label">Название</label>
            <input type="text" class="form-control" name="name" id="editPromptName" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Промт</label>
            <textarea class="form-control" name="prompt" id="editPromptText" rows="10" required></textarea>
        </div>

        <div class="text-end">
            <button type="button" class="btn btn-secondary" onclick="hideEditForm()">Отмена</button>
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </div>
    </form>
</div>

<script>
    function showEditForm(id, name, prompt) {
        document.getElementById('editPromptId').value = id;
        document.getElementById('editPromptName').value = name;
        document.getElementById('editPromptText').value = prompt;
        document.getElementById('editForm').style.display = 'block';
    }

    function hideEditForm() {
        document.getElementById('editForm').style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('click', function(e) {
            if (e.target.closest('.edit-prompt-btn')) {
                const button = e.target.closest('.edit-prompt-btn');
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');
                const prompt = button.getAttribute('data-prompt');

                showEditForm(id, name, prompt);
            }
        });
    });
</script>
