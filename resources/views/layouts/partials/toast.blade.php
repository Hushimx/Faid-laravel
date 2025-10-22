<script>
    window.toast = function (type = 'success', message = '', opts = {}) {
        const Toast = Swal.mixin({
            toast: true,
            position: opts.position || 'top-right',
            showConfirmButton: false,
            timer: opts.timer || 2500,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.style.cursor = 'pointer';
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
                toast.addEventListener('click', () => Swal.close());
            }
        });
        return Toast.fire({icon: type, title: message});
    };
    @if (session('success'))
    window.toast('success', @json(session('success')));
    @endif

    @if (session('error'))
    window.toast('error', @json(session('error')));
    @endif

    @if (session('warning'))
    window.toast('warning', @json(session('warning')));
    @endif

    @if (session('info'))
    window.toast('info', @json(session('info')));
    @endif
</script>
