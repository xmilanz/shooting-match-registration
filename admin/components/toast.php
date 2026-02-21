    <?php if ($toast): ?>
        <div class="toast-container shadow position-fixed start-50 translate-middle-x toast-top-10">
            <div class="toast text-bg-<?= htmlspecialchars($toast['type']) ?> border-0"
                role="alert"
                aria-live="assertive"
                aria-atomic="true"
                data-bs-autohide="true"
                data-bs-delay="<?= (int)$toast['duration'] ?>">
                <div class="d-flex">
                    <div class="toast-body fs-6 mt-1 mb-1 text-center">
                        <?= htmlspecialchars($toast['message']) ?>
                        <div class="progress mt-3 ms-2" style="height: 2px;">
                            <div class="progress-bar bg-<?= htmlspecialchars($toast['type']) ?>" role="progressbar" style="width: 100%;" id="toastProgress"></div>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white m-3" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>
        <script>
            const toastEl = document.querySelector('.toast');
            const toast = new bootstrap.Toast(toastEl);
            const progressBar = document.getElementById('toastProgress');

            const duration = parseInt(toastEl.getAttribute('data-bs-delay')) || 1500; // ms
            const interval = 50; // ms
            let elapsed = 0;

            toast.show();

            const timer = setInterval(() => {
                elapsed += interval;
                const percent = Math.max(0, 100 - (elapsed / duration) * 100);
                progressBar.style.width = percent + '%';

                if (elapsed >= duration) {
                    clearInterval(timer);
                }
            }, interval);
        </script>

    <?php endif; ?>