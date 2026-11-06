<?php
use App\Configuracion\Herramientas;
?>

<div class="page-head">
    <div>
        <span class="eyebrow">Unidades físicas</span>
        <h1>Inventario</h1>
        <p>
            Las unidades se crean desde <a class="text-link" href="index.php?page=motos">Gestión de motos</a>
            (por color) con un código automático. Aquí puedes reemplazarlo por el número de chasis o
            de motor y corregir el color.
        </p>
    </div>
    <span class="inventory-total" data-total-inventario><?= count($elementos) ?> unidades mostradas</span>
</div>

<section class="panel" id="stock-por-color">
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

<section class="panel" id="unidades-inventario">
    <form method="get" action="index.php#unidades-inventario" class="inventory-filter" role="search" data-filtro-inventario>
        <input type="hidden" name="page" value="inventario">
        <label class="inventory-filter-search" for="buscar-inventario">
            <span>Marca, modelo o código de serie</span>
            <input id="buscar-inventario" name="inventario_q" type="search" maxlength="100"
                   placeholder="Ej.: Pulsar NS160, azul o NC-55-003" list="inventario-sugerencias"
                   autocomplete="off" value="<?= Herramientas::escaparHtml($busquedaInventario) ?>">
            <datalist id="inventario-sugerencias">
                <?php foreach (array_unique(array_column($elementos, 'codigo_serie')) as $codigoSugerido): ?>
                    <option value="<?= Herramientas::escaparHtml($codigoSugerido) ?>"></option>
                <?php endforeach; ?>
                <?php foreach (array_unique(array_map(
                    static fn (array $unidad): string => $unidad['marca'] . ' ' . $unidad['modelo'],
                    $elementos
                )) as $modeloSugerido): ?>
                    <option value="<?= Herramientas::escaparHtml($modeloSugerido) ?>"></option>
                <?php endforeach; ?>
            </datalist>
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
            <a class="button subtle" href="index.php?page=inventario#unidades-inventario">Mostrar todos</a>
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
                <?php $puedeEditarUnidades = $usuarioPuede('inventario.gestionar'); ?>
                <?php foreach ($elementos as $unidadInventario): ?>
                    <?php
                    $claseEstado = 'amber';
                    if ($unidadInventario['estado'] === 'Disponible') {
                        $claseEstado = 'green';
                    } elseif ($unidadInventario['estado'] === 'Vendida') {
                        $claseEstado = 'red';
                    }
                    ?>
                    <tr id="unidad-<?= (int) $unidadInventario['id'] ?>" class="inventory-unit-row">
                        <td>
                            <div class="table-product">
                                <img src="<?= Herramientas::escaparHtml(Herramientas::rutaImagen($unidadInventario['imagen'])) ?>"
                                     alt="">
                                <b><?= Herramientas::escaparHtml($unidadInventario['marca'] . ' ' . $unidadInventario['modelo']) ?></b>
                            </div>
                        </td>
                        <?php
                        $coloresDeLaMoto = $coloresInventario[(int) $unidadInventario['moto_id']] ?? [];
                        $esEditable = $puedeEditarUnidades && $unidadInventario['estado'] !== 'Vendida';
                        $idFormulario = 'form-unidad-' . (int) $unidadInventario['id'];
                        ?>
                        <td>
                            <?php if ($esEditable && $coloresDeLaMoto): ?>
                                <select name="color_id" form="<?= $idFormulario ?>"
                                        aria-label="Color de la unidad <?= Herramientas::escaparHtml($unidadInventario['codigo_serie']) ?>">
                                    <option value="">Color por asignar</option>
                                    <?php foreach ($coloresDeLaMoto as $colorMoto): ?>
                                        <option value="<?= (int) $colorMoto['id'] ?>"
                                            <?= (int) $colorMoto['id'] === (int) $unidadInventario['color_id'] ? 'selected' : '' ?>>
                                            <?= Herramientas::escaparHtml($colorMoto['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <span class="color-dot" style="--swatch-color:<?= Herramientas::escaparHtml($unidadInventario['codigo_hex'] ?? '#cccccc') ?>"></span>
                                <?= Herramientas::escaparHtml($unidadInventario['color'] ?? 'Color por asignar') ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($esEditable): ?>
                                <form method="post" id="<?= $idFormulario ?>" class="inventory-unit-form">
                                    <?= Herramientas::campoCsrf() ?>
                                    <input type="hidden" name="action" value="actualizar_unidad">
                                    <input type="hidden" name="unidad_id" value="<?= (int) $unidadInventario['id'] ?>">
                                    <input type="hidden" name="inventario_q" value="<?= Herramientas::escaparHtml($busquedaInventario) ?>">
                                    <input type="hidden" name="estado_inventario" value="<?= Herramientas::escaparHtml($estadoInventario) ?>">
                                    <input name="codigo_serie" required maxlength="50" minlength="3"
                                           pattern="[A-Za-z0-9][A-Za-z0-9-]{2,49}" autocomplete="off"
                                           value="<?= Herramientas::escaparHtml($unidadInventario['codigo_serie']) ?>"
                                           title="Número de chasis (VIN), de motor o código interno"
                                           aria-label="Código de serie de la unidad">
                                    <button class="button subtle small-button">Guardar</button>
                                </form>
                            <?php else: ?>
                                <?= Herramientas::escaparHtml($unidadInventario['codigo_serie']) ?>
                            <?php endif; ?>
                        </td>
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
        Las unidades vendidas ya no se pueden modificar.
    </p>
</section>
