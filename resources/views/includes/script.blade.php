<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>  <!-- Memuat jQuery -->

<script src="{{ asset('assets/js/app.js') }}"></script>

{{-- <script src="{{ asset('js/app.js') }}"></script> --}}


<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>
<script src="{{ asset('assets/js/tables.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        $(document).on("click", ".delete-btn", function () {
            let mekanikId = $(this).data("id");

            Swal.fire({
                title: "Apakah Anda yakin?",
                text: "Data akan dihapus secara permanen!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Ya, hapus!",
                cancelButtonText: "Batal",
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(`delete-form-${mekanikId}`).submit();
                }
            });
        });
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const imageModal = document.getElementById("imageModal");
        const modalImage = document.getElementById("modalImage");
    
        document.querySelectorAll(".preview-image").forEach(image => {
            image.addEventListener("click", function () {
                const imageUrl = this.getAttribute("data-image");
                modalImage.setAttribute("src", imageUrl);
            });
        });
    });
    </script>

<script>
    function printCard() {
        var printContents = document.getElementById("printableArea").innerHTML;
        var originalContents = document.body.innerHTML;

        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        location.reload(); 
    }
</script>


    