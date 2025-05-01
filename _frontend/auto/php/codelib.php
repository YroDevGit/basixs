<?php
if(! function_exists("code_library")){
    function code_library(){
        ?>
        <link rel="stylesheet" href="<?=assets('code/datatable.css')?>" />
        <script src="<?=assets('code/swal.js')?>"></script>
        <script src="<?=assets('code/jquery.js')?>"></script>
        <script src="<?=assets('code/datatable.js')?>"></script>
        <script src="<?=assets('code/jspost.js')?>"></script>
        <?php
    }
}

?>