const AJAX_FORM_SELECTOR = '[data-ajax-form]';

let toastTimer = null;

function getToast() {
    let toast = document.getElementById('ajax-form-toast');

    if (toast) {
        return toast;
    }

    toast = document.createElement('div');
    toast.id = 'ajax-form-toast';
    toast.className =
        'pointer-events-none fixed bottom-5 right-5 z-[100] hidden max-w-sm rounded-xl border px-4 py-3 text-sm font-semibold shadow-xl';
    toast.setAttribute('role', 'status');
    toast.setAttribute('aria-live', 'polite');
    document.body.appendChild(toast);

    return toast;
}

function showToast(message, type = 'success') {
    const toast = getToast();

    window.clearTimeout(toastTimer);
    toast.textContent = message;
    toast.classList.remove(
        'hidden',
        'border-emerald-200',
        'bg-emerald-50',
        'text-emerald-800',
        'border-red-200',
        'bg-red-50',
        'text-red-800'
    );

    toast.classList.add(
        ...(type === 'error'
            ? ['border-red-200', 'bg-red-50', 'text-red-800']
            : ['border-emerald-200', 'bg-emerald-50', 'text-emerald-800'])
    );

    toastTimer = window.setTimeout(() => {
        toast.classList.add('hidden');
    }, 3000);
}

function extractErrorMessage(responseText, contentType) {
    if (contentType.includes('application/json')) {
        try {
            const payload = JSON.parse(responseText);

            if (payload.message) {
                return payload.message;
            }

            const firstError = Object.values(payload.errors ?? {})
                .flat()
                .find(Boolean);

            if (firstError) {
                return firstError;
            }
        } catch (_) {
            // ใช้ข้อความสำรองด้านล่าง
        }
    }

    if (contentType.includes('text/html')) {
        const responseDocument = new DOMParser().parseFromString(
            responseText,
            'text/html'
        );
        const alert = responseDocument.querySelector(
            '[role="alert"], [data-error-message]'
        );

        if (alert?.textContent.trim()) {
            return alert.textContent.trim();
        }
    }

    return 'ไม่สามารถดำเนินการได้ กรุณาลองใหม่';
}

function replaceTarget(responseText, targetSelector) {
    if (!targetSelector) {
        return;
    }

    const responseDocument = new DOMParser().parseFromString(
        responseText,
        'text/html'
    );
    const currentTarget = document.querySelector(targetSelector);
    const nextTarget = responseDocument.querySelector(targetSelector);

    if (!currentTarget || !nextTarget) {
        throw new Error('บันทึกสำเร็จ แต่ไม่พบส่วนข้อมูลที่ต้องอัปเดต');
    }

    currentTarget.replaceWith(nextTarget);
}

document.addEventListener('submit', async (event) => {
    const form = event.target.closest(AJAX_FORM_SELECTOR);

    if (!form || form.dataset.ajaxBusy === 'true') {
        return;
    }

    const confirmation = form.dataset.ajaxConfirm;

    if (confirmation && !window.confirm(confirmation)) {
        event.preventDefault();
        return;
    }

    event.preventDefault();

    const submitButton =
        event.submitter ?? form.querySelector('[type="submit"]');
    const submitLabel = submitButton?.querySelector(
        '[data-ajax-submit-label]'
    );
    const originalButtonHtml = submitButton?.innerHTML;
    const originalLabel = submitLabel?.textContent;

    form.dataset.ajaxBusy = 'true';

    if (submitButton) {
        submitButton.disabled = true;
        submitButton.classList.add('cursor-wait', 'opacity-70');

        if (submitLabel) {
            submitLabel.textContent =
                form.dataset.ajaxLoading || 'กำลังบันทึก...';
        }
    }

    try {
        const response = await fetch(form.action, {
            method: form.method.toUpperCase(),
            body: form.method.toUpperCase() === 'GET'
                ? null
                : new FormData(form),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json,text/html;q=0.9,application/xhtml+xml;q=0.8',
            },
            credentials: 'same-origin',
        });
        const contentType = response.headers.get('content-type') ?? '';
        const responseText = await response.text();

        if (!response.ok) {
            throw new Error(
                extractErrorMessage(responseText, contentType)
            );
        }

        replaceTarget(responseText, form.dataset.ajaxTarget);
        showToast(
            form.dataset.ajaxSuccess || 'บันทึกข้อมูลเรียบร้อยแล้ว'
        );

        document.dispatchEvent(
            new CustomEvent('ajax-form:success', {
                detail: { form, response },
            })
        );
    } catch (error) {
        if (submitButton && originalButtonHtml !== undefined) {
            submitButton.innerHTML = originalButtonHtml;
        }

        showToast(
            error?.message || 'ไม่สามารถดำเนินการได้ กรุณาลองใหม่',
            'error'
        );
    } finally {
        form.dataset.ajaxBusy = 'false';

        if (submitButton?.isConnected) {
            submitButton.disabled = false;
            submitButton.classList.remove('cursor-wait', 'opacity-70');

            if (submitLabel && originalLabel !== undefined) {
                submitLabel.textContent = originalLabel;
            }
        }
    }
});
