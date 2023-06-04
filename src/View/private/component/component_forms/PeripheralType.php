<?php $this->layout('layouts::component_form', ['title' => $title, 'user' => $user]) ?>

<h3 class="text-center"><?= $title ?></h3>
<div id="alert-container"></div>
<form id='component-form' method="POST" enctype="multipart/form-data">
    <?php
    if (isset($_GET['id'])) {
    ?>
        <div class="form-outline mb-4">
            <label class="form-label" for="PeripheralTypeID">IDENTIFICATIVO COMPONENTE</label>
            <input type="number" min="1" name="PeripheralTypeID" id="PeripheralTypeID" class="form-control" readonly="readonly" />
        </div>
    <?php } ?>
    <div class="form-outline mb-4">
        <label class="form-label" for="Name">Nome del tipo di periferica</label>
        <input type="text" name="Name" id="Name" class="form-control" required />
    </div>
    <input type='hidden' name='category' value='PeripheralType'>
    <input type='submit' class='btn btn-primary' id='btn-submit'>
    <input type='reset' class='btn btn-info' id='btn-reset'>
</form>