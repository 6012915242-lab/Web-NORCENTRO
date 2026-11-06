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
                <select name="moto_id" required data-moto-inventario>
                    <option value="">Seleccionar modelo</option>
                    <?php foreach ($motosInventario as $moto): ?>
                        <option value="<?= (int) $moto['id'] ?>">
                            <?= Herramientas::escaparHtml($moto['marca'] . ' ' . $moto['modelo']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Color
                <select name="color_id" data-color-inventario>
                    <option value="">Color por asignar</option>
                    <?php foreach ($coloresInventario as $idMotoColor => $coloresMoto): ?>
                        <?php foreach ($coloresMoto as $colorMoto): ?>
                            <option value="<?= (int) $colorMoto['id'] ?>" data-moto="<?= (int) $idMotoColor ?>" hidden>
                                <?= Herramientas::escaparHtml($colorMoto['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </select>
                <small>Solo aparecen los colores registrados para el modelo elegido.</small>
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
    <div class="panel-head">
        <div>
            <h2>Stock por color</h2>
            <p class="muted">Cuántas unidades hay de cada modelo y color.</p>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Motocicleta</th>
                    <th>Color</th>
                    <th>Disponibles</th>
                    <th>Reservadas</th>
                    <th>Vendidas</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stockPorColor as $filaStock): ?>
                    <tr>
                        <td><?= Herramientas::escaparHtml($filaStock['marca'] . ' ' . $filaStock['modelo']) ?></td>
                        <td>
                            <span class="color-dot" style="--swatch-color:<?= Herramientas::escaparHtml($filaStock['codigo_hex'] ?? '#cccccc') ?>"></span>
                            <?= Herramientas::escaparHtml($filaStock['color']) ?>
                        </td>
                        <td><b><?= (int) $filaStock['disponibles'] ?></b></td>
                        <td><?= (int) $filaStock['reservadas'] ?></td>
                        <td><?= (int) $filaStock['vendidas'] ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$stockPorColor): ?>
                    <tr>
                        <td colspan="5" class="empty">Todavía no hay unidades registradas.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="panel">
    <form method="get" action="index.php" class="inventory-filter" role="search">
        <input type="hidden" name="page" value="inventario">
        <label class="inventory-filter-search" for="buscar-inventario">
            <span>Marca, modelo o código de serie</span>
            <input id="buscar-inventario" name="inventario_q" type="search" maxlength="100"
                   placeholder="Ej.: Pulsar NS160" value="<?= Herramientas::escaparHtml($busquedaInventario) ?>">
        </label>
        <label class="inventory-filter-state" for="estado-inventario">
            <span>Estado</span>
            <select id="estado-inventario" name="estado_inventario">
                <option value="">Todos</option>
                <?php foreach (['Disponible', 'Reservada', 'Vendida'] as $estado): ?>
                    <option value="<?= $estado ?>" <?= $estadoInventario === $estado ? 'selected' : '' ?>>
                        <?= $estado ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <div class="inventory-filter-actions">
            <button class="button primary">Buscar</button>
            <a class="button subtle" href="index.php?page=inventario">Mostrar todos</a>
        </div>
    </form>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Motocicleta</th>
                    <th>Color</th>
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
                        <td>
                            <?php $coloresDeLaMoto = $coloresInventario[(int) $unidadInventario['moto_id']] ?? []; ?>
                            <?php if ($coloresDeLaMoto && $unidadInventario['estado'] !== 'Vendida'): ?>
                                <form method="post" class="inventory-color-form">
                                    <?= Herramientas::campoCsrf() ?>
                                    <input type="hidden" name="action" value="asignar_color_unidad">
                                    <input type="hidden" name="unidad_id" value="<?= (int) $unidadInventario['id'] ?>">
                                    <select name="color_id" aria-label="Color de la unidad <?= Herramientas::escaparHtml($unidadInventario['codigo_serie']) ?>"
                                            data-enviar-al-cambiar>
                                        <option value="">Color por asignar</option>
                                        <?php foreach ($coloresDeLaMoto as $colorMoto): ?>
                                            <option value="<?= (int) $colorMoto['id'] ?>"
                                                <?= (int) $colorMoto['id'] === (int) $unidadInventario['color_id'] ? 'selected' : '' ?>>
                                                <?= Herramientas::escaparHtml($colorMoto['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <noscript><button class="button subtle">Guardar</button></noscript>
                                </form>
                            <?php else: ?>
                                <?= Herramientas::escaparHtml($unidadInventario['color'] ?? 'Color por asignar') ?>
                            <?php endif; ?>
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
                        <td colspan="4" class="empty">No se encontraron unidades para esta búsqueda.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <p class="muted small inventory-note">
        Las ventas y los pedidos pagados actualizan el estado de cada unidad automáticamente.
    </p>
</section>
