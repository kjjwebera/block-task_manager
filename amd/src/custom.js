


// require.config({
//     paths: {
//         'jquery': 'https://code.jquery.com/jquery-3.6.0.min',
//         'datatables': 'https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min',
//         'datatablesButtons': 'https://cdn.datatables.net/buttons/2.2.0/js/dataTables.buttons.min', 
//         'jszip': 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min',
//         'pdfMake': 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min',
//         'css': 'https://cdnjs.cloudflare.com/ajax/libs/require-css/0.1.10/css.min',
//         'buttonsCSS': 'https://cdn.datatables.net/buttons/2.2.0/css/buttons.dataTables.min.css'
//     }
// });



// require(['jquery', 'datatables','datatablesButtons','jszip','pdfMake','buttonsCSS'], function($) {
//     console.log('jQuery and DataTables loaded');
    
//     $('.generaltable').DataTable({
//         paging: true,
//         searching: true,
//         ordering: true,
//         info: true,
//         dom: 'Bfrtip',
//         buttons: [
//             'csv',  // Export to CSV
//             'excel',
//             'pdf'
//         ]
//     });
//     new $.fn.dataTable.Buttons(table, {
//         buttons: [
//             'csv', 'excel', 'pdf'
//         ]
//     });

//     // Append the buttons to the container (make sure you have this div in your HTML)
//     table.buttons().container().appendTo('#button-container');
// });

// require.config({
//     paths: {
//         'jquery': 'https://code.jquery.com/jquery-3.6.0.min',
//         'datatables': 'https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min',
//         'datatablesButtons': 'https://cdn.datatables.net/buttons/2.2.0/js/dataTables.buttons.min', 
//         'jszip': 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min',
//         'css': 'https://cdnjs.cloudflare.com/ajax/libs/require-css/0.1.10/css.min',
//         'buttonsCSS': 'https://cdn.datatables.net/buttons/3.2.1/css/buttons.dataTables'  // CSS file
//     }
// });

// // Load the CSS using require-css
// require(['css!buttonsCSS'], function() {
//     console.log('Buttons CSS loaded');
    
//     // Now load other dependencies after buttonsCSS is loaded
//     require(['jquery', 'datatables', 'datatablesButtons', 'jszip',], function($) {
//         console.log('jQuery and DataTables loaded');
        
//         // Initialize the DataTable
//         var table = $('.generaltable').DataTable({
//             paging: true,
//             searching: true,
//             ordering: true,
//             info: true,
//             dom: 'Bfrtip',  // This 'B' will show buttons at the top
//             buttons: [
//                 'csv',   // Export to CSV
//                 'excel', // Export to Excel
//             ]
//         });

//         // Append the buttons to a specific container (optional)
//         table.buttons().container().appendTo('#button-container');
//     });
// });

// require.config({
//     paths: {
//         'jquery': 'https://code.jquery.com/jquery-3.6.0.min',
//         'datatables': 'https://cdn.datatables.net/2.2.1/js/dataTables',
//         'jszip': 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min',
//         'datatablesButtons': 'https://cdn.datatables.net/buttons/3.2.1/js/dataTables.buttons',
//         'css': 'https://cdnjs.cloudflare.com/ajax/libs/require-css/0.1.10/css.min',
//         'buttonsCSS': 'https://cdn.datatables.net/buttons/3.2.1/css/buttons.dataTables',
//     },
//     shim: {
//         'datatables': {
//             deps: ['jquery'],
//             exports: 'jQuery.fn.dataTable'  // Ensure DataTables is exposed via jQuery
//         },
//         'datatablesButtons': {
//             deps: ['datatables', 'jszip'],
//             exports: 'jQuery.fn.dataTable.Buttons'  // Ensure Buttons is also exposed via jQuery
//         }
//     }
// });

// $(document).ready(function(){
// require(['css!buttonsCSS'], function() {
//     console.log('Buttons CSS loaded');
    
//     require(['jquery', 'datatables', 'datatablesButtons', 'jszip'], function($) {
//         console.log('jQuery, DataTables, and DataTables Buttons loaded');
        
//         // Initialize DataTable
//         var table = $('.generaltable').DataTable({
//             buttons: [
//                     'csv',   // Export to CSV
//                     'excel' // Export to Excel
//                             ]
//         });
//         console.log('DataTable initialized');
        
//         // Debug: Check if buttons are available
//         // console.log(table, table.buttons());

//         // Ensure the buttons container is properly appended
//         // var buttonsContainer = table.buttons().container();
//         // console.log('Buttons container:', buttonsContainer);

//         // // Append buttons to the container
//         // buttonsContainer.appendTo('#button-container');
//     });
// });

// })


// var table = $('.generaltable').DataTable({
//     'dom': 'Blfrtip',
//                 buttons: [
//                         'csv',   // Export to CSV
//                         'excel' // Export to Excel
//                                 ]
//             });
    const markBoxes = document.querySelectorAll('.mark-box');
    markBoxes.forEach(function (markBox) {
        markBox.addEventListener('input', function () {
            const row = this.closest('tr');
            const totalMarkCell = row.querySelector('td:nth-child(6)'); // Get the total marks cell
            const totalMark = parseInt(totalMarkCell.textContent.trim(), 10);
            this.value = this.value.replace(/[^0-9]/g, '');  // Allow only numbers
            const enteredMark = parseInt(this.value, 10);

            if (isNaN(enteredMark)) {
                alert('Please enter a valid numeric value.');
                this.value = '';  // Clear if not a valid number
                return;
            }

            if (enteredMark > totalMark) {
                alert('Marks cannot exceed the total marks (' + totalMark + ').');
                this.value = totalMark;  // Reset to the maximum value
            }
        });
    });

    // Enable editing of readonly input fields when "Edit" link is clicked
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('edit-link')) {
            e.preventDefault();

            const submissionId = e.target.getAttribute('data-id'); // Get the submission ID
            const inputField = document.querySelector(`input[name="marks[${submissionId}]"]`);

            if (inputField) {
                // Enable the input field and focus on it
                inputField.removeAttribute('readonly');
                inputField.focus();

                // Hide the edit link
                e.target.style.display = 'none';
            }
        }
    });

