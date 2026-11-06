<?php
use App\Configuracion\Herramientas;
?>

<div class="page-head">
    <div>
        <span class="eyebrow">Unidades físicas</span>
        <h1>Inventario</h1>
        <p>Registra cada unidad por su código de serie y consulta su estado actual.</p>
    </div>
    <span class="inventory-total"><?= count($elementos) ?> unidades mostradas</span>
</div>

<?php if ($usuarioPuede('inventario.gestionar')): ?>
    <section class="panel inventory-register">
        <div class="panel-head">
            <div>
                <h2>Registrar unidad</h2>
                <p class="muted">La unidad se guardará en la base de datos como disponible.</p>
            </div>
        </div>

        <form method="post" class="form-grid">
            <?= Herramientas::campoCsrf() ?>
            <input type="hidden" name="action" value="agregar_unidad_inventario">

            <label>
                Motocicleta
                <select name="moto_id" required>
                    <option value="">Seleccionar modelo</option>
                    <?php foreach ($motosInventario as $moto): ?>
                        <option value="<?= (int) $moto['id'] ?>">
                            <?= Herramientas::escaparHtml($moto['marca'] . ' ' . $moto['modelo']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Código de serie
                <input name="codigo_serie" maxlength="50" minlength="3"
                       pattern="[A-Za-z0-9][A-Za-z0-9-]{2,49}" autocomplete="off" required>
                <small>Usa el número de serie de la unidad.</small>
            </label>

            <div class="form-actions full">
                <button class="button primary">Guardar en inventario</button>
            </div>
        </form>
    </section>
<?php endif; ?>

<section class="panel">
    <form method="get" action="index.php" class="search-form" role="search">
        <input type="hidden" name="page" value="inventario">
        <label for="buscar-inventario">Marca, modelo o código de serie</label>
        <input id="buscar-inventario" name="inventario_q" type="search" maxlength="100"
               value="<?= Herramientas::escaparHtml($busquedaInventario) ?>">
        <label for="estado-inventario">Estado</label>
        <select id="estado-inventario" name="estado_inventario">
            <option value="">Todos</option>
            <?php foreach (['Disponible', 'Reservada', 'Vendida'] as $estado): ?>
                <option value="<?= $estado ?>" <?= $estadoInventario === $estado ? 'selected' : '' ?>>
                    <?= $estado ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button class="button small-button">Buscar</button>
        <a href="index.php?page=inventario">Mostrar todos</a>
    </form>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Motocicleta</th>
                    <th>Código de serie</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($elementos as $unidadInventario): ?>
                    <?php
                    $claseEstado = 'amber';
                    if ($unidadInventario['estado'] === 'Disponible') {
                        $claseEstado = 'green';
                    } elseif ($unidadInventario['estado'] === 'Vendida') {
                        $claseEstado = 'red';
                    }
                    ?>
                    <tr>
                        <td>
                            <div class="table-product">
                                <img src="<?= Herramientas::escaparHtml(Herramientas::rutaImagen($unidadInventario['imagen'])) ?>"
                                     alt="">
                                <b><?= Herramientas::escaparHtml($unidadInventario['marca'] . ' ' . $unidadInventario['modelo']) ?></b>
                            </div>
                        </td>
                        <td><?= Herramientas::escaparHtml($unidadInventario['codigo_serie']) ?></td>
                        <td>
                            <span class="pill <?= $claseEstado ?>">
                                <?= Herramientas::escaparHtml($unidadInventario['estado']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$elementos): ?>
                    <tr>
                        <td colspan="3" class="empty">No se encontraron unidades para esta búsqueda.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <p class="muted small inventory-note">
        Las ventas y los pedidos pagados actualizan el estado de cada unidad automáticamente.
    </p>
</section>
