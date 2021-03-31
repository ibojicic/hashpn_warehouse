
$(document).ready(function() {
    var contheight = $(window).height() - 230;
    var mainTable = $('#MainTable').DataTable( {
        "autoWidth": true,
        "info": false,
        "jQueryUI": true,
        "ordering": false,
        "paging": false,
        "scrollX": "100%",
        "scrollY": contheight,
        "scrollCollapse": true,
        "searching": false,
      
        
        "columnDefs": [
        {
            "width": "1px"
            ,"targets": "_all" 
        }]
        
        
            
    } ) ;
    //new $.fn.dataTable.FixedColumns( mainTable );
    //mainTable.columns.adjust().draw();

} );

$(document).ready(function() {
    $('#accolist').DataTable();
} );

$(function() {
    $( "#accolists" ).accordion();
});