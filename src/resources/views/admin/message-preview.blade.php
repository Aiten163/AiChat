<div class="d-flex align-items-center justify-content-between">
    <div class="flex-grow-1 me-3">
        <pre class="mb-0" style="white-space: pre-wrap; font-size: 0.875rem;"><code>{{ $shortMessage }}</code></pre>
    </div>
    <div>
        <button type="button"
                class="btn btn-sm btn-outline-primary"
                data-bs-toggle="modal"
                data-bs-target="#messageModal-{{ $messageId }}">
            Развернуть
        </button>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="messageModal-{{ $messageId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Сообщение #{{ $messageId }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <pre style="white-space: pre-wrap; font-size: 0.875rem;"><code>{{ $fullMessage }}</code></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
