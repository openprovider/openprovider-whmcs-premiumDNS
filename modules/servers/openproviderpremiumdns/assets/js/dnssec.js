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
        .then(response => response.text())
        .then(html => {
            location.reload(); // Optional: Replace with dynamic UI update
        })
        .catch(error => {
            console.error('DNSSEC toggle failed:', error);
            errorBox.classList.remove('hidden');
            errorMsg.innerText = 'DNSSEC toggle failed. Please try again.';
        })
        .finally(() => {
            loader.style.display = 'none';
            submitBtn.disabled = false;
        });
    });
});