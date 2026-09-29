document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('dnssecToggleForm');
    const submitBtn = document.getElementById('dnssecToggleBtn');
    const loader = document.getElementById('dnssecLoading');
    const errorBox = document.querySelector('.dnssec-alert-error-message');
    const errorMsg = document.getElementById('dnssecErrorMessage');

    if (!form || !submitBtn) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        loader.style.display = 'inline-block';
        submitBtn.disabled = true;
        errorBox.classList.add('hidden');
        errorMsg.innerText = '';

        const formData = new FormData(form);
        formData.append("token", window.csrfToken);
        const serviceId = form.querySelector('input[name="id"]').value;
        const url = `clientarea.php?action=productdetails&modop=custom&a=toggle_dnssec&id=${encodeURIComponent(
          serviceId
        )}`;

        fetch(url, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.text().then(text => {
            if (!response.ok) {
                throw new Error(text || 'DNSSEC toggle failed. Please try again.');
            }
            return text;
        }))
        .then(text => {
            if (text.trim() === 'success') {
                location.reload();
                return;
            }

            // The module returned an error message instead of "success".
            throw new Error(text.trim() || 'DNSSEC toggle failed. Please try again.');
        })
        .catch(error => {
            console.error('DNSSEC toggle failed:', error);
            errorBox.classList.remove('hidden');
            errorMsg.innerText = error.message || 'DNSSEC toggle failed. Please try again.';
        })
        .finally(() => {
            loader.style.display = 'none';
            submitBtn.disabled = false;
        });
    });
});