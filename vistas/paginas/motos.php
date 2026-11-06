<?php
use App\Configuracion\Herramientas;

$formularioMoto = $editMoto ?? [];
$esAdministrador = $usuarioPuede('motos.gestionar');
?>
<div class="page-head">
  <div>
    <span class="eyebrow">Catálogo e inventario</span>
    <h1>Motocicletas</h1>
    <p>Administra modelos, colores, fotografías y unidades disponibles.</p>
  </div>
</div>

<div class="split-layout moto-admin-layout">
  <section class="panel">
    <div class="panel-head">
      <div>
        <span class="eyebrow">Ficha del modelo</span>
        <h2><?= $editMoto ? 'Editar motocicleta' : 'Registrar motocicleta' ?></h2>
      </div>
    </div>

    <?php if ($esAdministrador): ?>
      <form
        method="post"
        enctype="multipart/form-data"
        class="form-grid moto-admin-form"
        data-moto-gallery-form
      >
        <?= Herramientas::campoCsrf() ?>
        <input type="hidden" name="action" value="guardar_moto">
        <input type="hidden" name="id" value="<?= (int) ($formularioMoto['id'] ?? 0) ?>">

        <label>
          Marca
          <select name="marca_id" required>
            <option value="">Seleccionar</option>
            <?php foreach ($marcas as $marca): ?>
              <option
                value="<?= (int) $marca['id'] ?>"
                <?= (int) ($formularioMoto['marca_id'] ?? 0) === (int) $marca['id'] ? 'selected' : '' ?>
              ><?= Herramientas::escaparHtml($marca['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </label>

        <label>
          Modelo
          <input
            name="modelo"
            maxlength="100"
            required
            value="<?= Herramientas::escaparHtml($formularioMoto['modelo'] ?? '') ?>"
          >
        </label>

        <label>
          Cilindrada (cc)
          <input
            type="number"
            min="1"
            max="2000"
            name="cilindrada"
            required
            value="<?= Herramientas::escaparHtml($formularioMoto['cilindrada'] ?? '') ?>"
          >
        </label>

        <label>
          Año
          <input
            type="number"
            min="1970"
            max="<?= (int) date('Y') + 2 ?>"
            name="anio"
            required
            value="<?= Herramientas::escaparHtml($formularioMoto['anio'] ?? date('Y')) ?>"
          >
        </label>

        <label>
          Precio (S/)
          <input
            type="number"
            step="0.01"
            min="0.01"
            name="precio"
            required
            value="<?= Herramientas::escaparHtml($formularioMoto['precio'] ?? '') ?>"
          >
        </label>

        <label class="full">
          Descripción
          <textarea name="descripcion" rows="3" maxlength="1200"><?= Herramientas::escaparHtml($formularioMoto['descripcion'] ?? '') ?></textarea>
        </label>

        <label class="full upload-principal">
          Foto principal del catálogo
          <input
            type="file"
            name="imagen_principal"
            accept="image/jpeg,image/png,image/webp"
          >
          <small>Opcional si vas a subir fotografías dentro del primer color. Máximo 5 MB.</small>
        </label>

        <section class="moto-gallery-admin full" data-color-gallery>
          <header class="moto-gallery-admin-head">
            <div>
              <span class="eyebrow">Galería de colores y ángulos</span>
              <h3>Fotografías por cada color</h3>
              <p>Registra frente, laterales, parte posterior y detalles. Puedes subir hasta 10 tomas por color.</p>
            </div>
            <button class="button subtle small-button" type="button" data-add-color>
              + Agregar color
            </button>
          </header>

          <?php if (!empty($coloresEdicion)): ?>
            <div class="moto-existing-colors">
              <?php foreach ($coloresEdicion as $colorExistente): ?>
                <article>
                  <span
                    class="moto-existing-swatch"
                    style="--existing-color: <?= Herramientas::escaparHtml($colorExistente['codigo_hex']) ?>"
                  ></span>
                  <div>
                    <strong><?= Herramientas::escaparHtml($colorExistente['nombre']) ?></strong>
                    <small>
                      <?= count($colorExistente['imagenes']) ?> fotografía(s) ·
                      <?= (int) ($disponiblesPorColor[(int) $colorExistente['id']] ?? 0) ?> disponible(s)
                    </small>
                  </div>
                  <label class="moto-color-units">
                    Agregar unidades
                    <input
                      type="number"
                      name="unidades_existentes[<?= (int) $colorExistente['id'] ?>]"
                      min="0"
                      max="200"
                      value="0"
                      aria-label="Unidades nuevas de color <?= Herramientas::escaparHtml($colorExistente['nombre']) ?>"
                    >
                  </label>
                </article>
              <?php endforeach; ?>
            </div>
            <p class="moto-gallery-help">
              Para sumar unidades a un color existente, escribe la cantidad en su casilla.
              Para agregar más fotos a un color, escribe abajo exactamente el mismo nombre.
            </p>
          <?php endif; ?>

          <div class="moto-color-groups" data-color-groups>
            <fieldset class="moto-color-upload" data-color-group>
              <legend>Color 1</legend>
              <div class="moto-color-fields">
                <label>
                  Nombre del color
                  <input
                    name="colores[0][nombre]"
                    maxlength="50"
                    placeholder="Ejemplo: Rojo / gris"
                    <?= !$editMoto ? 'required' : '' ?>
                  >
                </label>
                <label>
                  Muestra
                  <input name="colores[0][codigo_hex]" type="color" value="#c92836">
                </label>
                <label class="moto-color-units-field">
                  Unidades de este color
                  <input name="colores[0][unidades]" type="number" min="0" max="200" value="0">
                  <small>Se crean con código de serie automático. Déjalo en 0 si aún no hay stock.</small>
                </label>
              </div>
              <label class="moto-angle-files">
                Tomas de este color
                <input
                  type="file"
                  name="imagenes_color[0][]"
                  accept="image/jpeg,image/png,image/webp"
                  multiple
                  <?= !$editMoto ? 'required' : '' ?>
                >
                <small data-file-count>Selecciona entre 1 y 10 fotografías.</small>
              </label>
              <button class="moto-remove-color" type="button" data-remove-color hidden>
                Quitar este color
              </button>
            </fieldset>
          </div>
        </section>

        <div class="form-actions full">
          <button class="button primary">
            <?= $editMoto ? 'Guardar cambios y galería' : 'Registrar moto y galería' ?>
          </button>
          <?php if ($editMoto): ?>
            <a class="button subtle" href="index.php?page=motos">Cancelar</a>
          <?php endif; ?>
        </div>
      </form>
    <?php else: ?>
      <p class="muted">
        Tu rol puede consultar el catálogo y la disponibilidad. Solo administración registra
        o modifica modelos.
      </p>
    <?php endif; ?>
  </section>

  <section class="panel">
    <div class="panel-head">
      <h2>Catálogo registrado</h2>
      <form class="search-form" method="get">
        <input type="hidden" name="page" value="motos">
        <input
          name="q"
          placeholder="Buscar marca o modelo"
          value="<?= Herramientas::escaparHtml($busquedaMotos) ?>"
        >
        <button class="button small-button">Buscar</button>
      </form>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Moto</th>
            <th>Precio</th>
            <th>Disponibles</th>
            <?php if ($esAdministrador): ?>
              <th>Acciones</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($elementos as $moto): ?>
            <tr>
              <td>
                <div class="table-product">
                  <img
                    src="<?= Herramientas::escaparHtml(Herramientas::rutaImagen($moto['imagen'])) ?>"
                    alt=""
                  >
                  <span>
                    <b><?= Herramientas::escaparHtml($moto['marca'] . ' ' . $moto['modelo']) ?></b>
                    <small><?= (int) $moto['cilindrada'] ?> cc · <?= Herramientas::escaparHtml($moto['anio']) ?></small>
                  </span>
                </div>
              </td>
              <td><?= Herramientas::formatearDinero($moto['precio']) ?></td>
              <td>
                <span class="pill <?= (int) $moto['disponibles'] ? 'green' : 'gray' ?>">
                  <?= (int) $moto['disponibles'] ?>
                </span>
              </td>
              <?php if ($esAdministrador): ?>
                <td>
                  <div class="actions">
                    <a href="index.php?page=motos&amp;edit=<?= (int) $moto['id'] ?>">Editar</a>
                    <form method="post" onsubmit="return confirm('¿Desactivar este modelo?')">
                      <?= Herramientas::campoCsrf() ?>
                      <input type="hidden" name="action" value="desactivar_moto">
                      <input type="hidden" name="id" value="<?= (int) $moto['id'] ?>">
                      <button class="danger-link">Desactivar</button>
                    </form>
                  </div>
                </td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>

          <?php if (!$elementos): ?>
            <tr><td colspan="4" class="empty">No hay motocicletas para mostrar.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>
