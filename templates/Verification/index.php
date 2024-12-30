<div class="users form">
    <?php echo $this->Flash->render(); ?>
    <h3>Proporcione un Token</h3>
    <?php echo $this->Form->create(); ?>

    <fieldset>
        <legend><?php echo __('Proporcione el token generado en su telefono móvil'); ?></legend>
        <div style="text-align: center">
            <img src="<?= 'data:image/png;base64,' . $imageQr ?>" alt="qr">
        </div>
        <?= $this->Form->control('token', ['require' => true, 'placeholder' => 'requerido']) ?>
    </fieldset>
    <?php echo $this->Form->submit(__('Verificar')); ?>
    <?php echo $this->Form->end(); ?>
</div>
