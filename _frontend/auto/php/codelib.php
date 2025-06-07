<?php

if(! function_exists("code_library")){
    function code_library(){
        import_swal();   // Remove this if you already have SweetAlert included
        import_jquery();   // Remove this if you already have jQuery included
        import_datatable();   // Remove this if you don't need DataTables
        import_jspost();
    }
}





?>