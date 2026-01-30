// $(document).ready(function() {
//     $('#table-2').DataTable({
//         responsive: true, // Enable responsive design
//         pagingType: 'simple', // Optional: Customize paging
//         language: {
//             // Optional: Customize language settings
//             paginate: {
//                 first: 'First',
//                 last: 'Last',
//                 next: 'Next',
//                 previous: 'Previous'
//             }
//         }
//     });
// });

$("#table-2").dataTable({
  "columnDefs": [
    { "orderable": false, "targets": [0] }
  ]
});
