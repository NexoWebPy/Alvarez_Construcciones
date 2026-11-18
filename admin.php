<?php
require_once 'seguridad.php';
ac_iniciar_sesion();

if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol'] ?? null) !== 'admin') {
    header('Location: login.php');
    exit();
}

require_once 'conexion.php';

$mensaje = '';
$tipo_mensaje = 'exito';

function ac_admin_mensaje(string $texto, string $tipo = 'exito'): void
{
    global $mensaje, $tipo_mensaje;
    $mensaje = $texto;
    $tipo_mensaje = $tipo;
}

function ac_admin_limpiar_archivo_nuevo(?string $ruta): void
{
    if ($ruta && is_file($ruta)) {
        @unlink($ruta);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!ac_validar_csrf($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        ac_admin_mensaje('La sesión expiró. Actualizá la página e intentá de nuevo.', 'error');
    } else {
        $accion = (string)($_POST['accion'] ?? 'subir_archivo');

        /* ===== CREAR PROYECTO PARA UN CLIENTE ===== */
        if ($accion === 'crear_proyecto') {
            $nombre_proyecto = trim(
                (string)($_POST['nombre_proyecto'] ?? '')
            );

            $cliente_id = filter_var(
                $_POST['cliente_id'] ?? null,
                FILTER_VALIDATE_INT
            );

            if (
                $nombre_proyecto === '' ||
                mb_strlen($nombre_proyecto, 'UTF-8') > 200
            ) {
                ac_admin_mensaje(
                    'Ingresá un nombre de proyecto válido.',
                    'error'
                );
            } elseif (
                $cliente_id === false ||
                $cliente_id === null ||
                $cliente_id < 1
            ) {
                ac_admin_mensaje(
                    'Seleccioná un cliente válido.',
                    'error'
                );
            } else {
                try {
                    $stmt = $conexion->prepare(
                        "SELECT id
                         FROM usuarios
                         WHERE id = ? AND rol = 'cliente'
                         LIMIT 1"
                    );

                    $stmt->bind_param('i', $cliente_id);
                    $stmt->execute();

                    $cliente = $stmt->get_result()->fetch_assoc();
                    $stmt->close();

                    if (!$cliente) {
                        ac_admin_mensaje(
                            'El cliente seleccionado no existe.',
                            'error'
                        );
                    } else {
                        $stmt = $conexion->prepare(
                            'INSERT INTO proyectos
                            (
                                nombre_proyecto,
                                cliente_id,
                                monto_gastado,
                                porcentaje_avance
                            )
                            VALUES (?, ?, 0, 0)'
                        );

                        $stmt->bind_param(
                            'si',
                            $nombre_proyecto,
                            $cliente_id
                        );

                        if ($stmt->execute()) {
                            ac_admin_mensaje(
                                'Proyecto creado correctamente.'
                            );
                        } else {
                            error_log(
                                'Error al crear proyecto: ' .
                                $stmt->error
                            );

                            ac_admin_mensaje(
                                'No se pudo crear el proyecto.',
                                'error'
                            );
                        }

                        $stmt->close();
                    }
                } catch (Throwable $e) {
                    error_log(
                        'Error al crear proyecto: ' .
                        $e->getMessage()
                    );

                    ac_admin_mensaje(
                        'Ocurrió un error al crear el proyecto.',
                        'error'
                    );
                }
            }
        }

        /* ===== CREAR CUENTA DE CLIENTE ===== */
        elseif ($accion === 'crear_cliente') {
            $nombre = trim((string)($_POST['nombre'] ?? ''));
            $email = trim((string)($_POST['email'] ?? ''));
            $password = (string)($_POST['password'] ?? '');

            if ($nombre === '' || mb_strlen($nombre, 'UTF-8') > 150) {
                ac_admin_mensaje('Ingresá un nombre válido de hasta 150 caracteres.', 'error');
            } elseif (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                ac_admin_mensaje('Ingresá un correo electrónico válido.', 'error');
            } elseif (strlen($password) < 12 || strlen($password) > 4096) {
                ac_admin_mensaje('La contraseña debe tener al menos 12 caracteres.', 'error');
            } else {
                $stmt = $conexion->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $email_existe = $stmt->get_result()->num_rows > 0;
                $stmt->close();

                if ($email_existe) {
                    ac_admin_mensaje('Ya existe una cuenta registrada con ese correo.', 'error');
                } else {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    if ($password_hash === false) {
                        ac_admin_mensaje('No se pudo generar la contraseña segura.', 'error');
                    } else {
                        $rol = 'cliente';
                        $stmt = $conexion->prepare(
                            'INSERT INTO usuarios (nombre, email, password, rol, creado_en) VALUES (?, ?, ?, ?, NOW())'
                        );
                        $stmt->bind_param('ssss', $nombre, $email, $password_hash, $rol);
                        if ($stmt->execute()) {
                            ac_admin_mensaje('La cuenta del cliente se creó correctamente.');
                        } else {
                            error_log('Error al crear cliente: ' . $stmt->error);
                            ac_admin_mensaje('No se pudo crear la cuenta del cliente.', 'error');
                        }
                        $stmt->close();
                    }
                }
            }
        }

        /* ===== ACTUALIZAR AVANCE, GASTO, HISTORIAL Y NOTIFICACIÓN ===== */
        elseif ($accion === 'actualizar_avance') {
            $proyecto_id = filter_var($_POST['proyecto_id'] ?? null, FILTER_VALIDATE_INT);
            $monto_gastado = filter_var($_POST['monto_gastado'] ?? null, FILTER_VALIDATE_FLOAT);
            $porcentaje_avance = filter_var($_POST['porcentaje_avance'] ?? null, FILTER_VALIDATE_INT);
            $descripcion_avance = trim((string)($_POST['descripcion_avance'] ?? ''));

            if ($proyecto_id === false || $proyecto_id === null || $proyecto_id < 1) {
                ac_admin_mensaje('Seleccioná un proyecto válido.', 'error');
            } elseif ($monto_gastado === false || $monto_gastado === null || !is_finite((float)$monto_gastado) || $monto_gastado < 0) {
                ac_admin_mensaje('Ingresá un monto gastado válido.', 'error');
            } elseif ($porcentaje_avance === false || $porcentaje_avance === null || $porcentaje_avance < 0 || $porcentaje_avance > 100) {
                ac_admin_mensaje('El avance debe estar entre 0 y 100%.', 'error');
            } elseif (mb_strlen($descripcion_avance, 'UTF-8') > 2000) {
                ac_admin_mensaje('La descripción no puede superar los 2.000 caracteres.', 'error');
            } else {
                try {
                    $conexion->begin_transaction();

                    $stmt = $conexion->prepare(
                        'SELECT cliente_id, nombre_proyecto FROM proyectos WHERE id = ? FOR UPDATE'
                    );
                    $stmt->bind_param('i', $proyecto_id);
                    $stmt->execute();
                    $proyecto = $stmt->get_result()->fetch_assoc();
                    $stmt->close();

                    if (!$proyecto) {
                        throw new RuntimeException('El proyecto no existe.');
                    }

                    $stmt = $conexion->prepare(
                        'UPDATE proyectos SET monto_gastado = ?, porcentaje_avance = ? WHERE id = ?'
                    );
                    $stmt->bind_param('dii', $monto_gastado, $porcentaje_avance, $proyecto_id);
                    $stmt->execute();
                    $stmt->close();

                    $registrado_por = (int)$_SESSION['usuario_id'];
                    $stmt = $conexion->prepare(
                        'INSERT INTO historial_avances (proyecto_id, porcentaje_avance, descripcion, registrado_por) VALUES (?, ?, ?, ?)'
                    );
                    $stmt->bind_param('iisi', $proyecto_id, $porcentaje_avance, $descripcion_avance, $registrado_por);
                    $stmt->execute();
                    $stmt->close();

                    $usuario_id = (int)$proyecto['cliente_id'];
                    $titulo_notificacion = 'Actualización de tu obra';
                    $mensaje_notificacion = 'El proyecto "' . $proyecto['nombre_proyecto'] .
                        '" registra un avance del ' . $porcentaje_avance . '%.';
                    if ($descripcion_avance !== '') {
                        $mensaje_notificacion .= ' Detalle: ' . $descripcion_avance;
                    }
                    $enlace = 'perfil_cliente.php';

                    $stmt = $conexion->prepare(
                        'INSERT INTO notificaciones (usuario_id, titulo, mensaje, enlace) VALUES (?, ?, ?, ?)'
                    );
                    $stmt->bind_param('isss', $usuario_id, $titulo_notificacion, $mensaje_notificacion, $enlace);
                    $stmt->execute();
                    $stmt->close();

                    $conexion->commit();
                    ac_admin_mensaje('Avance actualizado, historial guardado y cliente notificado.');
                } catch (Throwable $e) {
                    try { $conexion->rollback(); } catch (Throwable $ignorado) {}
                    error_log('Error al actualizar proyecto ' . (int)$proyecto_id . ': ' . $e->getMessage());
                    ac_admin_mensaje('No se pudo completar la actualización. Verificá que existan las tablas de historial y notificaciones.', 'error');
                }
            }
        }

        /* ===== CREAR PRESUPUESTO Y NOTIFICAR CLIENTE ===== */
        elseif ($accion === 'crear_presupuesto') {
            $proyecto_id = filter_var($_POST['proyecto_id'] ?? null, FILTER_VALIDATE_INT);
            $titulo = trim((string)($_POST['titulo_presupuesto'] ?? ''));
            $descripcion = trim((string)($_POST['descripcion_presupuesto'] ?? ''));
            $monto_raw = trim((string)($_POST['monto_presupuesto'] ?? ''));

            if ($proyecto_id === false || $proyecto_id === null || $proyecto_id < 1) {
                ac_admin_mensaje('Seleccioná un proyecto válido.', 'error');
            } elseif ($titulo === '' || mb_strlen($titulo, 'UTF-8') > 150) {
                ac_admin_mensaje('El título es obligatorio y no puede superar 150 caracteres.', 'error');
            } elseif (mb_strlen($descripcion, 'UTF-8') > 4000) {
                ac_admin_mensaje('La descripción no puede superar 4.000 caracteres.', 'error');
            } elseif ($monto_raw === '' || !is_numeric($monto_raw) || !is_finite((float)$monto_raw) || (float)$monto_raw < 0) {
                ac_admin_mensaje('Ingresá un monto válido para el presupuesto.', 'error');
            } else {
                try {
                    $conexion->begin_transaction();
                    $stmt = $conexion->prepare(
                        'SELECT id, cliente_id, nombre_proyecto FROM proyectos WHERE id = ? FOR UPDATE'
                    );
                    $stmt->bind_param('i', $proyecto_id);
                    $stmt->execute();
                    $proyecto = $stmt->get_result()->fetch_assoc();
                    $stmt->close();

                    if (!$proyecto) {
                        throw new RuntimeException('El proyecto seleccionado no existe.');
                    }

                    $monto = (float)$monto_raw;
                    $estado = 'enviado';
                    $stmt = $conexion->prepare(
                        'INSERT INTO presupuestos (proyecto_id, titulo, descripcion, monto, estado, fecha_emision, fecha_actualizacion) VALUES (?, ?, ?, ?, ?, NOW(), NOW())'
                    );
                    $stmt->bind_param('issds', $proyecto_id, $titulo, $descripcion, $monto, $estado);
                    $stmt->execute();
                    $stmt->close();

                    $usuario_id = (int)$proyecto['cliente_id'];
                    $titulo_notificacion = 'Nuevo presupuesto disponible';
                    $mensaje_notificacion = 'Se cargó el presupuesto "' . $titulo .
                        '" para tu proyecto "' . $proyecto['nombre_proyecto'] . '".';
                    $enlace = 'perfil_cliente.php';
                    $stmt = $conexion->prepare(
                        'INSERT INTO notificaciones (usuario_id, titulo, mensaje, enlace) VALUES (?, ?, ?, ?)'
                    );
                    $stmt->bind_param('isss', $usuario_id, $titulo_notificacion, $mensaje_notificacion, $enlace);
                    $stmt->execute();
                    $stmt->close();

                    $conexion->commit();
                    ac_admin_mensaje('Presupuesto creado y notificación enviada al cliente.');
                } catch (Throwable $e) {
                    try { $conexion->rollback(); } catch (Throwable $ignorado) {}
                    error_log('Error al crear presupuesto: ' . $e->getMessage());
                    ac_admin_mensaje('No se pudo crear el presupuesto. Verificá que existan las tablas de presupuestos y notificaciones.', 'error');
                }
            }
        }

        /* ===== ACTUALIZAR ESTADO DEL PRESUPUESTO ===== */
        elseif ($accion === 'actualizar_presupuesto') {
            $presupuesto_id = filter_var($_POST['presupuesto_id'] ?? null, FILTER_VALIDATE_INT);
            $estado_nuevo = (string)($_POST['estado_presupuesto'] ?? '');
            $estados_permitidos = ['pendiente', 'enviado', 'aprobado', 'rechazado'];

            if ($presupuesto_id === false || $presupuesto_id === null || $presupuesto_id < 1) {
                ac_admin_mensaje('Seleccioná un presupuesto válido.', 'error');
            } elseif (!in_array($estado_nuevo, $estados_permitidos, true)) {
                ac_admin_mensaje('Seleccioná un estado válido.', 'error');
            } else {
                try {
                    $conexion->begin_transaction();
                    $stmt = $conexion->prepare(
                        'SELECT pr.id, pr.estado, pr.titulo, p.cliente_id, p.nombre_proyecto
                         FROM presupuestos pr
                         INNER JOIN proyectos p ON p.id = pr.proyecto_id
                         WHERE pr.id = ? FOR UPDATE'
                    );
                    $stmt->bind_param('i', $presupuesto_id);
                    $stmt->execute();
                    $presupuesto = $stmt->get_result()->fetch_assoc();
                    $stmt->close();

                    if (!$presupuesto) {
                        throw new RuntimeException('El presupuesto no existe.');
                    }

                    $estado_anterior = $presupuesto['estado'];
                    $stmt = $conexion->prepare(
                        'UPDATE presupuestos SET estado = ?, fecha_actualizacion = NOW() WHERE id = ?'
                    );
                    $stmt->bind_param('si', $estado_nuevo, $presupuesto_id);
                    $stmt->execute();
                    $stmt->close();

                    if ($estado_anterior !== $estado_nuevo) {
                        $usuario_id = (int)$presupuesto['cliente_id'];
                        $titulo_notificacion = 'Actualización de presupuesto';
                        $mensaje_notificacion = 'El presupuesto "' . $presupuesto['titulo'] .
                            '" de tu proyecto "' . $presupuesto['nombre_proyecto'] .
                            '" ahora figura como: ' . $estado_nuevo . '.';
                        $enlace = 'perfil_cliente.php';

                        $stmt = $conexion->prepare(
                            'INSERT INTO notificaciones (usuario_id, titulo, mensaje, enlace) VALUES (?, ?, ?, ?)'
                        );
                        $stmt->bind_param('isss', $usuario_id, $titulo_notificacion, $mensaje_notificacion, $enlace);
                        $stmt->execute();
                        $stmt->close();
                    }

                    $conexion->commit();
                    ac_admin_mensaje('Estado del presupuesto actualizado correctamente.');
                } catch (Throwable $e) {
                    try { $conexion->rollback(); } catch (Throwable $ignorado) {}
                    error_log('Error al actualizar presupuesto: ' . $e->getMessage());
                    ac_admin_mensaje('No se pudo actualizar el presupuesto. Revisá la estructura de las tablas.', 'error');
                }
            }
        }

        /* ===== EDITAR ARCHIVO EXISTENTE ===== */
        elseif ($accion === 'editar_archivo') {
            $archivo_id = filter_var($_POST['archivo_id'] ?? null, FILTER_VALIDATE_INT);
            $proyecto_id = filter_var($_POST['proyecto_id'] ?? null, FILTER_VALIDATE_INT);
            $tipo = (string)($_POST['tipo'] ?? '');
            $descripcion = trim((string)($_POST['descripcion'] ?? ''));

            if ($archivo_id === false || $archivo_id === null || $archivo_id < 1) {
                ac_admin_mensaje('Seleccioná un archivo válido.', 'error');
            } elseif ($proyecto_id === false || $proyecto_id === null || $proyecto_id < 1) {
                ac_admin_mensaje('Seleccioná un proyecto válido.', 'error');
            } elseif (!in_array($tipo, ['foto_avance', 'factura'], true)) {
                ac_admin_mensaje('Seleccioná un tipo de archivo válido.', 'error');
            } elseif (mb_strlen($descripcion, 'UTF-8') > 4000) {
                ac_admin_mensaje('La descripción no puede superar los 4.000 caracteres.', 'error');
            } else {
                $stmt = $conexion->prepare('SELECT id FROM proyectos WHERE id = ?');
                $stmt->bind_param('i', $proyecto_id);
                $stmt->execute();
                $proyecto_valido = $stmt->get_result()->num_rows === 1;
                $stmt->close();

                $stmt = $conexion->prepare('SELECT ruta_archivo, tipo FROM archivos WHERE id = ?');
                $stmt->bind_param('i', $archivo_id);
                $stmt->execute();
                $archivo_actual = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (!$proyecto_valido || !$archivo_actual) {
                    ac_admin_mensaje('No se encontró el proyecto o el archivo seleccionado.', 'error');
                } else {
                    $ruta_archivo = (string)$archivo_actual['ruta_archivo'];
                    $archivo_nuevo = false;
                    $ruta_destino_fs = null;
                    $archivo_temporal = $_FILES['archivo']['tmp_name'] ?? null;
                    $error_subida = $_FILES['archivo']['error'] ?? UPLOAD_ERR_NO_FILE;

                    if ($error_subida !== UPLOAD_ERR_NO_FILE) {
                        if ($error_subida !== UPLOAD_ERR_OK || !$archivo_temporal || !is_uploaded_file($archivo_temporal)) {
                            ac_admin_mensaje('No se pudo recibir el archivo nuevo.', 'error');
                        } else {
                            $tamano_archivo = filesize($archivo_temporal);
                            $mimes_permitidos = [
                                'image/jpeg' => 'jpg',
                                'image/png' => 'png',
                                'image/webp' => 'webp',
                                'application/pdf' => 'pdf'
                            ];
                            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo_temporal);

                            if ($tamano_archivo === false || $tamano_archivo > 10 * 1024 * 1024) {
                                ac_admin_mensaje('El archivo supera el límite de 10 MB.', 'error');
                            } elseif (!isset($mimes_permitidos[$mime])) {
                                ac_admin_mensaje('Formato no permitido. Utilizá JPG, PNG, WEBP o PDF.', 'error');
                            } elseif ($tipo === 'foto_avance' && $mime === 'application/pdf') {
                                ac_admin_mensaje('Una foto de avance debe ser una imagen JPG, PNG o WEBP.', 'error');
                            } else {
                                $directorio = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
                                if (!is_dir($directorio) && !mkdir($directorio, 0755, true) && !is_dir($directorio)) {
                                    ac_admin_mensaje('No se pudo preparar el almacenamiento del archivo.', 'error');
                                } else {
                                    $nombre_archivo = bin2hex(random_bytes(16)) . '.' . $mimes_permitidos[$mime];
                                    $ruta_archivo = 'uploads/' . $nombre_archivo;
                                    $ruta_destino_fs = $directorio . DIRECTORY_SEPARATOR . $nombre_archivo;
                                    if (move_uploaded_file($archivo_temporal, $ruta_destino_fs)) {
                                        $archivo_nuevo = true;
                                    } else {
                                        ac_admin_mensaje('No se pudo guardar el archivo nuevo.', 'error');
                                    }
                                }
                            }
                        }
                    } elseif ($tipo === 'foto_avance') {
                        $ruta_actual_fs = realpath(__DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $archivo_actual['ruta_archivo']));
                        if ($ruta_actual_fs && is_file($ruta_actual_fs) && (new finfo(FILEINFO_MIME_TYPE))->file($ruta_actual_fs) === 'application/pdf') {
                            ac_admin_mensaje('Una factura PDF no puede clasificarse como foto de avance.', 'error');
                        }
                    }

                    if ($tipo_mensaje !== 'error') {
                        $stmt = $conexion->prepare(
                            'UPDATE archivos SET proyecto_id = ?, tipo = ?, ruta_archivo = ?, descripcion = ? WHERE id = ?'
                        );
                        $stmt->bind_param('isssi', $proyecto_id, $tipo, $ruta_archivo, $descripcion, $archivo_id);

                        if ($stmt->execute()) {
                            ac_admin_mensaje('El archivo se actualizó correctamente.');
                            if ($archivo_nuevo) {
                                $directorio_uploads = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'uploads');
                                $ruta_anterior = realpath(__DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $archivo_actual['ruta_archivo']));
                                if ($directorio_uploads && $ruta_anterior &&
                                    strpos($ruta_anterior, $directorio_uploads . DIRECTORY_SEPARATOR) === 0 &&
                                    is_file($ruta_anterior)) {
                                    @unlink($ruta_anterior);
                                }
                            }
                        } else {
                            ac_admin_mensaje('No se pudieron guardar los cambios del archivo.', 'error');
                            if ($archivo_nuevo) {
                                ac_admin_limpiar_archivo_nuevo($ruta_destino_fs);
                            }
                        }
                        $stmt->close();
                    } elseif ($archivo_nuevo) {
                        ac_admin_limpiar_archivo_nuevo($ruta_destino_fs);
                    }
                }
            }
        }

        /* ===== SUBIR ARCHIVO NUEVO ===== */
        elseif ($accion === 'subir_archivo') {
            $proyecto_id = filter_var($_POST['proyecto_id'] ?? null, FILTER_VALIDATE_INT);
            $tipo = (string)($_POST['tipo'] ?? '');
            $descripcion = trim((string)($_POST['descripcion'] ?? ''));

            if ($proyecto_id === false || $proyecto_id === null || $proyecto_id < 1) {
                ac_admin_mensaje('Seleccioná un proyecto válido.', 'error');
            } elseif (!in_array($tipo, ['foto_avance', 'factura'], true)) {
                ac_admin_mensaje('Seleccioná un tipo de documento válido.', 'error');
            } elseif (mb_strlen($descripcion, 'UTF-8') > 4000) {
                ac_admin_mensaje('La descripción no puede superar los 4.000 caracteres.', 'error');
            } elseif (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK ||
                empty($_FILES['archivo']['tmp_name']) || !is_uploaded_file($_FILES['archivo']['tmp_name'])) {
                ac_admin_mensaje('Seleccioná un archivo válido de hasta 10 MB.', 'error');
            } else {
                $archivo_temporal = $_FILES['archivo']['tmp_name'];
                $tamano_archivo = filesize($archivo_temporal);
                $mimes_permitidos = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp',
                    'application/pdf' => 'pdf'
                ];
                $mime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo_temporal);

                $stmt = $conexion->prepare('SELECT id FROM proyectos WHERE id = ?');
                $stmt->bind_param('i', $proyecto_id);
                $stmt->execute();
                $proyecto_valido = $stmt->get_result()->num_rows === 1;
                $stmt->close();

                if (!$proyecto_valido) {
                    ac_admin_mensaje('Seleccioná un proyecto válido.', 'error');
                } elseif ($tamano_archivo === false || $tamano_archivo > 10 * 1024 * 1024) {
                    ac_admin_mensaje('El archivo supera el límite de 10 MB.', 'error');
                } elseif (!isset($mimes_permitidos[$mime])) {
                    ac_admin_mensaje('Formato no permitido. Utilizá JPG, PNG, WEBP o PDF.', 'error');
                } elseif ($tipo === 'foto_avance' && $mime === 'application/pdf') {
                    ac_admin_mensaje('La foto de avance debe ser una imagen JPG, PNG o WEBP.', 'error');
                } else {
                    $directorio = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
                    if (!is_dir($directorio) && !mkdir($directorio, 0755, true) && !is_dir($directorio)) {
                        ac_admin_mensaje('No se pudo preparar el almacenamiento del archivo.', 'error');
                    } else {
                        $nombre_archivo = bin2hex(random_bytes(16)) . '.' . $mimes_permitidos[$mime];
                        $ruta_destino = 'uploads/' . $nombre_archivo;
                        $ruta_destino_fs = $directorio . DIRECTORY_SEPARATOR . $nombre_archivo;

                        if (move_uploaded_file($archivo_temporal, $ruta_destino_fs)) {
                            $stmt = $conexion->prepare(
                                'INSERT INTO archivos (proyecto_id, tipo, ruta_archivo, descripcion) VALUES (?, ?, ?, ?)'
                            );
                            $stmt->bind_param('isss', $proyecto_id, $tipo, $ruta_destino, $descripcion);

                            if ($stmt->execute()) {
                                ac_admin_mensaje('¡Archivo subido y registrado con éxito!');
                            } else {
                                ac_admin_limpiar_archivo_nuevo($ruta_destino_fs);
                                ac_admin_mensaje('No se pudo registrar el archivo. Intentá de nuevo.', 'error');
                            }
                            $stmt->close();
                        } else {
                            ac_admin_mensaje('No se pudo guardar el archivo. Intentá de nuevo.', 'error');
                        }
                    }
                }
            }
        }

        /* ===== RESPONDER MENSAJE DE CLIENTE ===== */
        elseif ($accion === 'responder_mensaje') {
            $mensaje_id = filter_var($_POST['mensaje_id'] ?? null, FILTER_VALIDATE_INT);
            $respuesta = trim((string)($_POST['respuesta'] ?? ''));

            if ($mensaje_id === false || $mensaje_id === null || $mensaje_id < 1) {
                ac_admin_mensaje('Seleccioná un mensaje válido.', 'error');
            } elseif ($respuesta === '' || mb_strlen($respuesta, 'UTF-8') > 4000) {
                ac_admin_mensaje('Escribí una respuesta válida (máximo 4.000 caracteres).', 'error');
            } else {
                $stmt = $conexion->prepare(
                    'SELECT id, destinatario_id FROM mensajes WHERE id = ? LIMIT 1'
                );
                $stmt->bind_param('i', $mensaje_id);
                $stmt->execute();
                $mensaje_actual = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (!$mensaje_actual || $mensaje_actual['destinatario_id'] !== (int)$_SESSION['usuario_id']) {
                    ac_admin_mensaje('El mensaje no existe o no te pertenece.', 'error');
                } else {
                    $stmt = $conexion->prepare(
                        'UPDATE mensajes SET respuesta = ?, respondido_por = ?, respondido_en = NOW() WHERE id = ?'
                    );
                    $stmt->bind_param('sii', $respuesta, $_SESSION['usuario_id'], $mensaje_id);

                    if ($stmt->execute()) {
                        ac_admin_mensaje('Respuesta enviada correctamente.');
                    } else {
                        error_log('Error al responder mensaje: ' . $stmt->error);
                        ac_admin_mensaje('No se pudo enviar la respuesta. Intentá de nuevo.', 'error');
                    }
                    $stmt->close();
                }
            }
        } else {
            ac_admin_mensaje('La acción solicitada no es válida.', 'error');
        }
    }
}

/* ===== PERFIL DEL ADMINISTRADOR ===== */
$stmt = $conexion->prepare('SELECT nombre, email, rol FROM usuarios WHERE id = ? LIMIT 1');
$admin_id = (int)$_SESSION['usuario_id'];
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$perfil_admin = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$perfil_admin || $perfil_admin['rol'] !== 'admin') {
    $_SESSION = [];
    session_destroy();
    header('Location: login.php', true, 303);
    exit;
}

/* ===== PROYECTOS Y ARCHIVOS ===== */
$proyectos_resultado = $conexion->query(
    'SELECT p.id, p.nombre_proyecto, p.monto_gastado, p.porcentaje_avance, u.nombre AS cliente
     FROM proyectos p INNER JOIN usuarios u ON p.cliente_id = u.id ORDER BY p.id DESC'
);
$proyectos = $proyectos_resultado ? $proyectos_resultado->fetch_all(MYSQLI_ASSOC) : [];

$archivos_resultado = $conexion->query(
    'SELECT a.id, a.tipo, a.descripcion, a.fecha_subida, a.ruta_archivo,
            p.id AS proyecto_id, p.nombre_proyecto, u.nombre AS cliente
     FROM archivos a
     INNER JOIN proyectos p ON p.id = a.proyecto_id
     INNER JOIN usuarios u ON u.id = p.cliente_id
     ORDER BY a.fecha_subida DESC'
);
$archivos_admin = $archivos_resultado ? $archivos_resultado->fetch_all(MYSQLI_ASSOC) : [];


/* ===== CLIENTES DISPONIBLES ===== */
$resultado_clientes = $conexion->query(
    "SELECT id, nombre, email
     FROM usuarios
     WHERE rol = 'cliente'
     ORDER BY nombre ASC"
);

$clientes_admin = $resultado_clientes
    ? $resultado_clientes->fetch_all(MYSQLI_ASSOC)
    : [];

/* ===== MÉTRICAS DEL DASHBOARD ===== */
$metricas = [
    'proyectos' => 0,
    'clientes' => 0,
    'fotos' => 0,
    'facturas' => 0,
    'avance_promedio' => 0
];

$sql_metricas = "
    SELECT
        (SELECT COUNT(*) FROM proyectos) AS proyectos,
        (SELECT COUNT(*) FROM usuarios WHERE rol = 'cliente') AS clientes,
        (SELECT COUNT(*) FROM archivos WHERE tipo = 'foto_avance') AS fotos,
        (SELECT COUNT(*) FROM archivos WHERE tipo = 'factura') AS facturas,
        (SELECT COALESCE(ROUND(AVG(porcentaje_avance)), 0)
         FROM proyectos WHERE porcentaje_avance IS NOT NULL) AS avance_promedio
";
$resultado_metricas = $conexion->query($sql_metricas);
if ($resultado_metricas) {
    $datos_metricas = $resultado_metricas->fetch_assoc();
    foreach ($metricas as $clave => $valor) {
        $metricas[$clave] = (int)($datos_metricas[$clave] ?? 0);
    }
}

$sql_alertas = "
    SELECT p.id, p.nombre_proyecto, p.porcentaje_avance,
           MAX(a.fecha_subida) AS ultima_actualizacion
    FROM proyectos p
    LEFT JOIN archivos a ON a.proyecto_id = p.id
    GROUP BY p.id, p.nombre_proyecto, p.porcentaje_avance
    HAVING ultima_actualizacion IS NULL
        OR ultima_actualizacion < DATE_SUB(NOW(), INTERVAL 14 DAY)
    ORDER BY ultima_actualizacion ASC
";
$resultado_alertas = $conexion->query($sql_alertas);
$alertas_actividad = $resultado_alertas ? $resultado_alertas->fetch_all(MYSQLI_ASSOC) : [];

$sql_resumen_obras = "
    SELECT p.id, p.nombre_proyecto, p.monto_gastado, p.porcentaje_avance,
           u.nombre AS cliente, MAX(a.fecha_subida) AS ultima_actualizacion
    FROM proyectos p
    INNER JOIN usuarios u ON u.id = p.cliente_id
    LEFT JOIN archivos a ON a.proyecto_id = p.id
    GROUP BY p.id, p.nombre_proyecto, p.monto_gastado, p.porcentaje_avance, u.nombre
    ORDER BY p.id DESC
";
$resultado_resumen_obras = $conexion->query($sql_resumen_obras);
$resumen_obras = $resultado_resumen_obras ? $resultado_resumen_obras->fetch_all(MYSQLI_ASSOC) : [];
$actividad_reciente = array_slice($archivos_admin, 0, 5);

/* ===== PRESUPUESTOS ===== */
$presupuestos_admin = [];
$resultado_presupuestos = $conexion->query(
    "SELECT pr.id, pr.proyecto_id, pr.titulo, pr.descripcion, pr.monto,
            pr.estado, pr.fecha_emision, pr.fecha_actualizacion,
            p.nombre_proyecto, u.nombre AS cliente
     FROM presupuestos pr
     INNER JOIN proyectos p ON p.id = pr.proyecto_id
     INNER JOIN usuarios u ON u.id = p.cliente_id
     ORDER BY pr.fecha_emision DESC, pr.id DESC"
);
if ($resultado_presupuestos) {
    $presupuestos_admin = $resultado_presupuestos->fetch_all(MYSQLI_ASSOC);
}

/* ===== MENSAJES DE CLIENTES PARA EL ADMIN ===== */
$mensajes_admin = [];
$mensajes_no_leidos = 0;
$resultado_mensajes = $conexion->query(
    "SELECT m.id, m.asunto, m.mensaje, m.respuesta, m.leido, m.respondido_en,
            u.nombre AS remitente_nombre, u.email AS remitente_email, m.creado_en
     FROM mensajes m
     INNER JOIN usuarios u ON u.id = m.remitente_id
     WHERE m.destinatario_id = 1
     ORDER BY m.leido ASC, m.creado_en DESC"
);
if ($resultado_mensajes) {
    $mensajes_admin = $resultado_mensajes->fetch_all(MYSQLI_ASSOC);
    foreach ($mensajes_admin as $m) {
        if (!$m['leido']) {
            $mensajes_no_leidos++;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <title>AC Alvarez - Panel Administrador</title>
    <Style>
/* =====================================================
   AC CONSTRUCCIONES — ADMIN DASHBOARD
   Diseño premium | Negro, dorado y cristal
===================================================== */

:root {
    --ac-bg: #0b0d11;
    --ac-surface: rgba(23, 26, 33, 0.88);
    --ac-surface-hover: rgba(32, 36, 45, 0.96);
    --ac-gold: #c29547;
    --ac-gold-light: #f0c778;
    --ac-text: #f3f4f6;
    --ac-muted: #a3aab6;
    --ac-border: rgba(255, 255, 255, 0.10);
    --ac-success: #4ade80;
    --ac-danger: #f87171;
    --ac-radius: 18px;
    --ac-shadow: 0 18px 45px rgba(0, 0, 0, 0.25);
}

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

html {
    scroll-behavior: smooth;
    scroll-padding-top: 100px;
    height: 100%;
}

body {
    min-height: 100dvh;
    min-height: -webkit-fill-available;
    display: flex;
    flex-direction: column;
    font-family: 'Manrope', 'Avenir Next', sans-serif;
    color: var(--ac-text);
    overflow-x: hidden;
    background:
        linear-gradient(
            135deg,
            rgba(11, 13, 17, 0.94),
            rgba(10, 10, 12, 0.97)
        ),
        url('fondo1.jpeg') center / cover fixed no-repeat;
    line-height: 1.6;
    -webkit-font-smoothing: antialiased;
}

button,
input,
select,
textarea {
    font: inherit;
}

button,
a,
input,
select,
textarea {
    -webkit-tap-highlight-color: transparent;
}

a {
    color: inherit;
}

/* =====================================================
   HEADER
===================================================== */

header {
    position: sticky;
    top: 0;
    z-index: 100;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    padding: 17px clamp(18px, 4vw, 54px);
    background: rgba(12, 14, 18, 0.86);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    border-bottom: 1px solid var(--ac-border);
}

.logo-container {
    display: flex;
    align-items: center;
    gap: 13px;
    min-width: 0;
}

.logo-img {
    width: 43px;
    height: 43px;
    flex-shrink: 0;
    object-fit: cover;
    border-radius: 50%;
    background: #fff;
    border: 1px solid rgba(194, 149, 71, 0.55);
    box-shadow: 0 0 20px rgba(194, 149, 71, 0.16);
}

.brand-name {
    color: #fff;
    font-size: 1rem;
    font-weight: 800;
    letter-spacing: 1.4px;
    line-height: 1.3;
}

.user-info {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    flex-wrap: wrap;
    gap: 14px;
}

.user-name {
    color: #d1d5db;
    font-size: 0.85rem;
}

.btn-logout {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 40px;
    padding: 9px 17px;
    border: 1px solid rgba(248, 113, 113, 0.35);
    border-radius: 999px;
    background: rgba(239, 68, 68, 0.08);
    color: #fca5a5;
    text-decoration: none;
    font-size: 0.82rem;
    font-weight: 800;
    cursor: pointer;
    transition: background 0.2s ease,
                border-color 0.2s ease,
                transform 0.2s ease;
}

.btn-logout:hover {
    background: rgba(239, 68, 68, 0.17);
    border-color: rgba(248, 113, 113, 0.7);
    transform: translateY(-1px);
}

/* =====================================================
   CONTENEDOR PRINCIPAL
===================================================== */

.container {
    width: 100%;
    max-width: 1200px;
    margin: 24px auto;
    padding: 0 16px;
}

.admin-container {
    width: 100%;
    max-width: 1280px;
    padding: 0 16px;
    margin: 0 auto;
}

.admin-heading {
    margin-bottom: 30px;
}

.admin-heading h1 {
    margin-bottom: 9px;
    color: #fff;
    font-size: clamp(1.8rem, 3.5vw, 2.7rem);
    font-weight: 800;
    line-height: 1.2;
    letter-spacing: -1px;
}

.admin-heading p {
    max-width: 720px;
    color: var(--ac-muted);
    font-size: 0.95rem;
    line-height: 1.8;
}

/* =====================================================
   TARJETAS Y FORMULARIOS
===================================================== */

.admin-card,
.dashboard-panel {
    min-width: 0;
    padding: clamp(20px, 3vw, 32px);
    background: var(--ac-surface);
    border: 1px solid var(--ac-border);
    border-radius: var(--ac-radius);
    box-shadow: var(--ac-shadow);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    transition: border-color 0.25s ease;
}

.admin-card {
    margin-bottom: 22px;
}

.admin-card:hover,
.dashboard-panel:hover {
    border-color: rgba(194, 149, 71, 0.22);
}

.admin-card h2,
.dashboard-panel h2 {
    margin-bottom: 10px;
    color: #fff;
    font-size: 1.25rem;
    font-weight: 800;
    line-height: 1.4;
}

.admin-card > p {
    margin-bottom: 24px;
    color: var(--ac-muted);
    font-size: 0.9rem;
    line-height: 1.7;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #e5e7eb;
    font-size: 0.84rem;
    font-weight: 700;
}

.form-control {
    display: block;
    width: 100%;
    min-height: 47px;
    padding: 12px 14px;
    border: 1px solid rgba(255, 255, 255, 0.13);
    border-radius: 11px;
    outline: none;
    background: rgba(10, 12, 16, 0.75);
    color: #f9fafb;
    font-size: 0.9rem;
    transition: border-color 0.2s ease,
                box-shadow 0.2s ease,
                background 0.2s ease;
}

.form-control::placeholder {
    color: #747c89;
}

.form-control:focus {
    border-color: var(--ac-gold);
    background: rgba(10, 12, 16, 0.95);
    box-shadow: 0 0 0 3px rgba(194, 149, 71, 0.12);
}

.form-control option {
    background: #171a21;
    color: #fff;
}

textarea.form-control {
    min-height: 110px;
    resize: vertical;
}

input[type="file"].form-control {
    padding: 9px;
    color: var(--ac-muted);
}

input[type="file"]::file-selector-button {
    margin-right: 12px;
    padding: 9px 13px;
    border: 1px solid rgba(194, 149, 71, 0.55);
    border-radius: 8px;
    background: rgba(194, 149, 71, 0.12);
    color: var(--ac-gold-light);
    font-weight: 800;
    cursor: pointer;
    transition: background 0.2s ease;
}

input[type="file"]::file-selector-button:hover {
    background: rgba(194, 149, 71, 0.25);
}

/* Botones de formularios */

.btn-submit {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    min-height: 49px;
    margin-top: 8px;
    padding: 13px 20px;
    border: 1px solid transparent;
    border-radius: 11px;
    background: linear-gradient(
        110deg,
        #b78334,
        #d4a359 55%,
        #edc476
    );
    color: #17130c;
    font-size: 0.91rem;
    font-weight: 900;
    cursor: pointer;
    box-shadow: 0 7px 20px rgba(194, 149, 71, 0.15);
    transition: transform 0.2s ease,
                box-shadow 0.2s ease,
                filter 0.2s ease;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 26px rgba(194, 149, 71, 0.25);
    filter: brightness(1.06);
}

.btn-submit:active {
    transform: translateY(0);
}

button:focus-visible,
a:focus-visible,
input:focus-visible,
select:focus-visible,
textarea:focus-visible {
    outline: 2px solid var(--ac-gold-light);
    outline-offset: 3px;
}

/* =====================================================
   MENSAJES Y ALERTAS
===================================================== */

.alert {
    margin-bottom: 22px;
    padding: 14px 17px;
    border: 1px solid transparent;
    border-radius: 12px;
    font-size: 0.88rem;
    font-weight: 700;
    line-height: 1.6;
    overflow-wrap: anywhere;
}

.alert-exito,
.alert-success {
    border-color: rgba(34, 197, 94, 0.35);
    background: rgba(34, 197, 94, 0.09);
    color: var(--ac-success);
}

.alert-error,
.alert-danger {
    border-color: rgba(239, 68, 68, 0.35);
    background: rgba(239, 68, 68, 0.09);
    color: #fca5a5;
}

/* =====================================================
   DASHBOARD — INDICADORES
===================================================== */

.dashboard-section {
    margin: 30px 0;
}

.dashboard-section-title {
    margin-bottom: 17px;
    color: #f9fafb;
    font-size: 1.2rem;
    font-weight: 800;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 17px;
}

.dashboard-stat {
    position: relative;
    min-width: 0;
    padding: 23px 20px;
    overflow: hidden;
    border: 1px solid var(--ac-border);
    border-radius: 16px;
    background: linear-gradient(
        145deg,
        rgba(29, 32, 41, 0.96),
        rgba(18, 20, 26, 0.96)
    );
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.16);
    transition: transform 0.22s ease,
                border-color 0.22s ease,
                background 0.22s ease;
}

.dashboard-stat::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 2px;
    background: linear-gradient(
        90deg,
        transparent,
        var(--ac-gold),
        transparent
    );
    opacity: 0.75;
}

.dashboard-stat:hover {
    transform: translateY(-4px);
    border-color: rgba(194, 149, 71, 0.42);
    background: var(--ac-surface-hover);
}

.dashboard-stat-label {
    color: #b8bec8;
    font-size: 0.78rem;
    font-weight: 700;
    line-height: 1.6;
}

.dashboard-stat-value {
    margin-top: 12px;
    color: var(--ac-gold-light);
    font-size: clamp(1.8rem, 2.5vw, 2.5rem);
    font-weight: 900;
    letter-spacing: -1px;
    line-height: 1.15;
    overflow-wrap: anywhere;
}

.dashboard-stat-note {
    margin-top: 8px;
    color: #89919e;
    font-size: 0.74rem;
    line-height: 1.5;
}

/* =====================================================
   BARRAS DE PROGRESO
===================================================== */

.dashboard-progress {
    width: 100%;
    height: 8px;
    margin-top: 11px;
    overflow: hidden;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.09);
}

.dashboard-progress > span {
    display: block;
    width: 0;
    height: 100%;
    border-radius: inherit;
    background: linear-gradient(
        90deg,
        #a9782d,
        var(--ac-gold),
        var(--ac-gold-light)
    );
    box-shadow: 0 0 12px rgba(194, 149, 71, 0.18);
    transition: width 0.5s ease;
}

/* =====================================================
   DASHBOARD — COLUMNAS Y PANELES
===================================================== */

.dashboard-columns {
    display: grid;
    grid-template-columns: minmax(0, 1.2fr) minmax(0, 1fr);
    align-items: start;
    gap: 22px;
    margin-top: 25px;
}

.dashboard-panel h2 {
    margin-bottom: 20px;
}

.dashboard-list {
    display: grid;
    gap: 13px;
}

.dashboard-item {
    min-width: 0;
    padding: 16px;
    border: 1px solid rgba(255, 255, 255, 0.07);
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.025);
    overflow-wrap: anywhere;
    transition: background 0.2s ease,
                border-color 0.2s ease;
}

.dashboard-item:hover {
    background: rgba(255, 255, 255, 0.045);
    border-color: rgba(194, 149, 71, 0.2);
}

.dashboard-item strong {
    display: block;
    margin-bottom: 7px;
    color: #f9fafb;
    font-size: 0.9rem;
    font-weight: 800;
}

.dashboard-muted {
    color: var(--ac-muted);
    font-size: 0.81rem;
    line-height: 1.75;
}

.dashboard-alert {
    border-left: 3px solid var(--ac-gold);
}

.dashboard-ok {
    padding: 16px;
    border: 1px solid rgba(34, 197, 94, 0.25);
    border-radius: 12px;
    background: rgba(34, 197, 94, 0.07);
    color: #86efac;
    font-size: 0.86rem;
    line-height: 1.7;
}

/* =====================================================
   ENLACES Y ACCESOS RÁPIDOS
===================================================== */

.dashboard-links {
    display: flex;
    flex-wrap: wrap;
    gap: 11px;
    margin-top: 19px;
}

.dashboard-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 40px;
    padding: 10px 14px;
    border: 1px solid rgba(194, 149, 71, 0.4);
    border-radius: 9px;
    background: rgba(194, 149, 71, 0.055);
    color: var(--ac-gold-light);
    text-decoration: none;
    font-size: 0.8rem;
    font-weight: 800;
    line-height: 1.4;
    transition: background 0.2s ease,
                border-color 0.2s ease,
                transform 0.2s ease;
}

.dashboard-link:hover {
    transform: translateY(-1px);
    border-color: var(--ac-gold);
    background: rgba(194, 149, 71, 0.13);
}

/* =====================================================
   TABLAS
===================================================== */

.dashboard-table-wrap {
    width: 100%;
    overflow-x: auto;
    border: 1px solid rgba(255, 255, 255, 0.07);
    border-radius: 12px;
    scrollbar-width: thin;
    scrollbar-color: var(--ac-gold) #171a21;
}

.dashboard-table {
    width: 100%;
    min-width: 650px;
    border-collapse: collapse;
    text-align: left;
}

.dashboard-table th,
.dashboard-table td {
    padding: 15px 14px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.075);
    vertical-align: middle;
}

.dashboard-table th {
    background: rgba(194, 149, 71, 0.055);
    color: var(--ac-gold-light);
    font-size: 0.75rem;
    font-weight: 900;
    letter-spacing: 0.3px;
    white-space: nowrap;
}

.dashboard-table td {
    color: #e5e7eb;
    font-size: 0.81rem;
    line-height: 1.6;
}

.dashboard-table tbody tr:last-child td {
    border-bottom: none;
}

.dashboard-table tbody tr {
    transition: background 0.2s ease;
}

.dashboard-table tbody tr:hover {
    background: rgba(255, 255, 255, 0.025);
}

/* =====================================================
   ARCHIVOS Y SECCIÓN DE ADMINISTRACIÓN
===================================================== */

.admin-files {
    margin-top: 30px;
}

.admin-files h2 {
    margin-bottom: 20px;
    color: #fff;
    font-size: 1.35rem;
    font-weight: 800;
}

/* Compatible con enlaces y botones existentes */

.admin-card a:not(.dashboard-link),
.admin-files a:not(.dashboard-link) {
    overflow-wrap: anywhere;
}

.admin-card img,
.admin-files img {
    max-width: 100%;
    height: auto;
    border-radius: 10px;
}

/* =====================================================
   SCROLLBAR
===================================================== */

* {
    scrollbar-width: thin;
    scrollbar-color: rgba(194, 149, 71, 0.65) rgba(255, 255, 255, 0.04);
}

::-webkit-scrollbar {
    width: 9px;
    height: 9px;
}

::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.035);
}

::-webkit-scrollbar-thumb {
    border: 2px solid var(--ac-bg);
    border-radius: 99px;
    background: rgba(194, 149, 71, 0.65);
}

::-webkit-scrollbar-thumb:hover {
    background: var(--ac-gold-light);
}

/* =====================================================
   RESPONSIVE — TABLETS
===================================================== */

@media (max-width: 1050px) {
    .dashboard-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .dashboard-columns {
        grid-template-columns: minmax(0, 1fr);
    }
}

/* =====================================================
   RESPONSIVE — MÓVILES
===================================================== */

@media (max-width: 650px) {
    header {
        align-items: flex-start;
        gap: 12px;
        padding: 14px 16px;
    }

    .logo-container {
        gap: 9px;
    }

    .logo-img {
        width: 36px;
        height: 36px;
    }

    .brand-name {
        font-size: 0.78rem;
        letter-spacing: 0.8px;
    }

    .user-info {
        gap: 8px;
    }

    .user-name {
        max-width: 115px;
        font-size: 0.72rem;
        overflow-wrap: anywhere;
    }

    .btn-logout {
        min-height: 35px;
        padding: 7px 11px;
        font-size: 0.72rem;
    }

    .container {
        width: calc(100% - 28px);
        margin: 25px auto;
    }

    .admin-heading {
        margin-bottom: 23px;
    }

    .admin-heading h1 {
        font-size: 1.8rem;
    }

    .admin-card,
    .dashboard-panel {
        padding: 19px;
        border-radius: 15px;
    }

    .admin-card h2,
    .dashboard-panel h2 {
        font-size: 1.1rem;
    }

    .dashboard-section {
        margin: 23px 0;
    }

    .dashboard-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 11px;
    }

    .dashboard-stat {
        padding: 17px 14px;
        border-radius: 13px;
    }

    .dashboard-stat-label {
        font-size: 0.72rem;
    }

    .dashboard-stat-value {
        font-size: 1.75rem;
    }

    .dashboard-stat-note {
        font-size: 0.69rem;
    }

    .dashboard-columns {
        gap: 16px;
        margin-top: 18px;
    }

    .dashboard-links {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
    }

    .dashboard-link {
        width: 100%;
        text-align: center;
    }

    .dashboard-item {
        padding: 13px;
    }

    .dashboard-table {
        min-width: 600px;
    }

    .dashboard-table th,
    .dashboard-table td {
        padding: 12px 10px;
    }
}

/* =====================================================
   PANTALLAS MUY PEQUEÑAS
===================================================== */

@media (max-width: 360px) {
    .dashboard-grid {
        grid-template-columns: minmax(0, 1fr);
    }

    header {
        flex-direction: column;
        align-items: stretch;
    }

    .user-info {
        justify-content: space-between;
    }

    .dashboard-stat-value {
        font-size: 2rem;
    }
}

/* =====================================================
   ACCESIBILIDAD — MOVIMIENTO REDUCIDO
===================================================== */

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        scroll-behavior: auto !important;
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}



/* =====================================================
   AC CONSTRUCCIONES — ARCHIVOS CARGADOS
===================================================== */

.admin-files {
    margin-top: 30px;
    padding: clamp(18px, 3vw, 30px);
    background: var(--ac-surface);
    border: 1px solid var(--ac-border);
    border-radius: var(--ac-radius);
    box-shadow: var(--ac-shadow);
    backdrop-filter: blur(14px);
}

.admin-files-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 14px;
    margin-bottom: 22px;
}

.admin-files-heading h2 {
    margin: 0;
    color: #fff;
    font-size: clamp(1.2rem, 2vw, 1.5rem);
    font-weight: 800;
}

.admin-files-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 32px;
    padding: 6px 12px;
    border: 1px solid rgba(194, 149, 71, 0.35);
    border-radius: 999px;
    background: rgba(194, 149, 71, 0.12);
    color: var(--ac-gold-light);
    font-size: 0.78rem;
    font-weight: 800;
    white-space: nowrap;
}

/* Lista y tarjetas */

.admin-file-list {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.admin-file-row {
    display: flex;
    flex-direction: column;
    min-width: 0;
    padding: 19px;
    border: 1px solid rgba(255, 255, 255, 0.09);
    border-radius: 15px;
    background: linear-gradient(145deg, #1f222b, #12141a);
    transition: transform 0.22s ease,
                border-color 0.22s ease,
                box-shadow 0.22s ease;
}

.admin-file-row:hover {
    transform: translateY(-2px);
    border-color: rgba(194, 149, 71, 0.42);
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.18);
}

.admin-file-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;
    min-width: 0;
}

.admin-file-title-group {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
    min-width: 0;
}

.admin-file-type {
    display: inline-flex;
    align-items: center;
    width: fit-content;
    max-width: 100%;
    padding: 6px 10px;
    border: 1px solid rgba(194, 149, 71, 0.24);
    border-radius: 7px;
    background: rgba(194, 149, 71, 0.12);
    color: var(--ac-gold-light);
    font-size: 0.7rem;
    font-weight: 900;
    letter-spacing: 0.35px;
    line-height: 1.4;
    text-transform: uppercase;
}

.admin-file-project {
    color: #f3f4f6;
    font-size: 0.91rem;
    font-weight: 800;
    line-height: 1.55;
    overflow-wrap: anywhere;
}

/* Botón ver archivo */

.admin-file-link {
    display: inline-flex;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    min-height: 38px;
    padding: 9px 12px;
    border: 1px solid rgba(194, 149, 71, 0.45);
    border-radius: 9px;
    background: linear-gradient(110deg, #b78334, #edc476);
    color: #17130c;
    font-size: 0.76rem;
    font-weight: 900;
    text-align: center;
    text-decoration: none;
    transition: filter 0.2s ease, transform 0.2s ease;
}

.admin-file-link:hover {
    filter: brightness(1.08);
    transform: translateY(-1px);
}

/* Fecha y descripción */

.admin-file-meta-group {
    display: grid;
    gap: 8px;
    margin-top: 17px;
    padding-top: 14px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.admin-file-meta,
.admin-file-description {
    margin: 0;
    overflow-wrap: anywhere;
}

.admin-file-meta {
    color: #a3aab6;
    font-size: 0.76rem;
    line-height: 1.6;
}

.admin-file-description {
    color: #d1d5db;
    font-size: 0.84rem;
    line-height: 1.65;
}

/* Formulario desplegable para editar */

.admin-file-edit {
    margin-top: 16px;
    border: 1px solid rgba(255, 255, 255, 0.09);
    border-radius: 10px;
    background: rgba(0, 0, 0, 0.15);
    overflow: hidden;
}

.admin-file-edit summary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 14px;
    color: var(--ac-gold-light);
    font-size: 0.82rem;
    font-weight: 800;
    cursor: pointer;
    list-style: none;
    transition: background 0.2s ease;
}

.admin-file-edit summary::-webkit-details-marker {
    display: none;
}

.admin-file-edit summary::after {
    content: "+";
    color: var(--ac-gold-light);
    font-size: 1.2rem;
}

.admin-file-edit[open] summary::after {
    content: "−";
}

.admin-file-edit summary:hover,
.admin-file-edit[open] summary {
    background: rgba(194, 149, 71, 0.08);
}

.admin-file-edit-panel {
    padding: 17px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.admin-file-edit-form .form-group:last-of-type {
    margin-bottom: 14px;
}

.admin-file-edit-form .btn-submit {
    margin-top: 0;
}

/* Estado vacío */

.empty-state {
    padding: 38px 20px;
    border: 1px dashed rgba(194, 149, 71, 0.35);
    border-radius: 12px;
    background: rgba(194, 149, 71, 0.04);
    color: var(--ac-muted);
    font-size: 0.88rem;
    line-height: 1.7;
    text-align: center;
}

/* Texto de ayuda de los formularios */

.upload-hint {
    display: block;
    margin-top: 8px;
    color: #9ca3af;
    font-size: 0.75rem;
    line-height: 1.65;
}

/* Accesibilidad */

.admin-file-edit summary:focus-visible,
.admin-file-link:focus-visible {
    outline: 2px solid var(--ac-gold-light);
    outline-offset: 3px;
}

/* Tabletas */

@media (max-width: 850px) {
    .admin-file-list {
        grid-template-columns: minmax(0, 1fr);
    }

    .admin-file-row {
        padding: 17px;
    }
}

/* Móviles */

@media (max-width: 520px) {
    .admin-files {
        padding: 16px;
        border-radius: 14px;
    }

    .admin-files-heading {
        align-items: flex-start;
        margin-bottom: 17px;
    }

    .admin-file-top {
        flex-direction: column;
        align-items: stretch;
    }

    .admin-file-link {
        align-self: flex-start;
        min-width: 120px;
    }

    .admin-file-project {
        font-size: 0.86rem;
    }

    .admin-file-edit-panel {
        padding: 13px;
    }
}

/* Movimiento reducido */

@media (prefers-reduced-motion: reduce) {
    .admin-file-row,
    .admin-file-link,
    .admin-file-edit summary {
        transition: none;
    }

    .admin-file-row:hover,
    .admin-file-link:hover {
        transform: none;
    }
}

/* ===== MENSAJES DE CLIENTES (ADMIN) ===== */
.admin-msg-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.admin-msg-count {
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--ac-muted);
}

.admin-msg-unread-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 999px;
    border: 1px solid rgba(194, 149, 71, 0.4);
    background: rgba(194, 149, 71, 0.12);
    color: var(--ac-gold-light);
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.admin-msg-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.admin-msg-item {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 20px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 16px;
    background: linear-gradient(145deg, rgba(255, 255, 255, 0.03), rgba(255, 255, 255, 0.01));
    transition: transform 0.22s ease, border-color 0.22s ease, box-shadow 0.22s ease;
}

.admin-msg-item:hover {
    transform: translateY(-2px);
    border-color: rgba(194, 149, 71, 0.32);
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.18);
}

.admin-msg-item.admin-msg-unread {
    border-left: 3px solid var(--ac-gold);
}

.admin-msg-item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.admin-msg-from {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}

.admin-msg-from-name {
    color: #fff;
    font-weight: 800;
    font-size: 0.95rem;
    line-height: 1.3;
}

.admin-msg-from-email {
    color: var(--ac-muted);
    font-size: 0.78rem;
}

.admin-msg-meta {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
}

.admin-msg-date {
    font-size: 0.75rem;
    color: var(--ac-muted);
    white-space: nowrap;
}

.admin-msg-status {
    font-size: 0.7rem;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 999px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    white-space: nowrap;
}

.admin-msg-status.admin-msg-unread-badge {
    background: rgba(194, 149, 71, 0.15);
    color: #d4a359;
    border: 1px solid rgba(194, 149, 71, 0.3);
}

.admin-msg-status.admin-msg-read-badge {
    background: rgba(34, 197, 94, 0.15);
    color: #86efac;
    border: 1px solid rgba(34, 197, 94, 0.3);
}

.admin-msg-item-body {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-width: 0;
}

.admin-msg-subject {
    margin: 0;
    color: #f3f4f6;
    font-weight: 800;
    font-size: 1.05rem;
    line-height: 1.4;
    overflow-wrap: anywhere;
}

.admin-msg-content {
    margin: 0;
    color: #e5e7eb;
    font-size: 0.88rem;
    line-height: 1.65;
    overflow-wrap: anywhere;
    white-space: pre-wrap;
}

.admin-msg-reply {
    padding: 14px;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
    background: rgba(0, 0, 0, 0.1);
    border-radius: 0 0 12px 12px;
}

.admin-msg-reply-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
}

.admin-msg-reply-label {
    font-size: 0.78rem;
    font-weight: 800;
    color: var(--ac-gold-light);
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.admin-msg-reply-date {
    font-size: 0.72rem;
    color: var(--ac-muted);
}

.admin-msg-reply-content {
    margin: 0;
    color: #e5e7eb;
    font-size: 0.88rem;
    line-height: 1.65;
    white-space: pre-wrap;
}

.admin-msg-reply-form summary {
    cursor: pointer;
    list-style: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.16);
    background: rgba(255, 255, 255, 0.06);
    color: #f3f4f6;
    font-weight: 600;
    transition: background 0.2s ease, border-color 0.2s ease;
}

.admin-msg-reply-form summary:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.22);
}

.admin-msg-reply-form summary::-webkit-details-marker {
    display: none;
}

.admin-msg-form {
    margin-top: 14px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.admin-msg-form .form-group {
    margin-bottom: 0;
}

.admin-msg-form .btn-submit {
    width: auto;
    align-self: flex-start;
}


</style>
   
    
</head>
<header>
    <div class="logo-container">
        <img src="CasaAlpinaLogo.jpeg" alt="AC Alvarez Construcciones" class="logo-img">
        <span class="brand-name">AC ALVAREZ</span>
    </div>
    <div class="user-info">
        <span class="user-name">Admin: <strong><?= htmlspecialchars($_SESSION['nombre'] ?? 'Administrador', ENT_QUOTES, 'UTF-8') ?></strong></span>
        <form action="logout.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(ac_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="btn-logout">Cerrar Sesión</button>
        </form>
    </div>
</header>

<main class="container admin-container">
    <div class="admin-heading">
        <h1>Panel de gestión de obras</h1>
        <p>Actualizá el progreso, los gastos, los presupuestos y los archivos de cada proyecto.</p>
    </div>

    <?php if ($mensaje !== ''): ?>
        <div class="alert alert-<?= htmlspecialchars($tipo_mensaje, ENT_QUOTES, 'UTF-8') ?>" role="status">
            <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <!-- Mensajes de clientes (Bandeja de entrada del admin) -->
    <section class="admin-card" id="mensajes-clientes">
        <h2>Mensajes de clientes</h2>
        <p>Consultá y respondé los mensajes que te envían los clientes desde su portal privado.</p>

        <div class="admin-msg-header">
            <span class="admin-msg-count">
                <?= count($mensajes_admin) ?> mensaje<?= count($mensajes_admin) !== 1 ? 's' : '' ?>
                <?php if ($mensajes_no_leidos > 0): ?>
                    <span class="admin-msg-unread-badge"><?= $mensajes_no_leidos ?> sin leer</span>
                <?php endif; ?>
            </span>
        </div>

        <?php if ($mensajes_admin): ?>
            <div class="admin-msg-list">
                <?php foreach ($mensajes_admin as $msg): ?>
                    <article class="admin-msg-item <?= !$msg['leido'] ? 'admin-msg-unread' : '' ?>">
                        <div class="admin-msg-item-header">
                            <div class="admin-msg-from">
                                <strong class="admin-msg-from-name"><?= htmlspecialchars($msg['remitente_nombre'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <span class="admin-msg-from-email"><?= htmlspecialchars($msg['remitente_email'], ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <div class="admin-msg-meta">
                                <span class="admin-msg-date"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($msg['creado_en'])), ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="admin-msg-status <?= !$msg['leido'] ? 'admin-msg-unread-badge' : 'admin-msg-read-badge' ?>">
                                    <?= !$msg['leido'] ? 'Sin leer' : 'Leído' ?>
                                </span>
                            </div>
                        </div>

                        <div class="admin-msg-item-body">
                            <p class="admin-msg-subject"><?= htmlspecialchars($msg['asunto'], ENT_QUOTES, 'UTF-8') ?></p>
                            <p class="admin-msg-content"><?= nl2br(htmlspecialchars($msg['mensaje'], ENT_QUOTES, 'UTF-8')) ?></p>
                        </div>

                        <?php if ($msg['respuesta'] !== null && $msg['respuesta'] !== ''): ?>
                            <div class="admin-msg-reply">
                                <div class="admin-msg-reply-header">
                                    <span class="admin-msg-reply-label">Respuesta enviada</span>
                                    <?php if ($msg['respondido_en']): ?>
                                        <span class="admin-msg-reply-date"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($msg['respondido_en'])), ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php endif; ?>
                                </div>
                                <p class="admin-msg-reply-content"><?= nl2br(htmlspecialchars($msg['respuesta'], ENT_QUOTES, 'UTF-8')) ?></p>
                            </div>
                        <?php else: ?>
                            <details class="admin-msg-reply-form">
                                <summary>Responder mensaje</summary>
                                <form action="admin.php" method="POST" class="admin-msg-form">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(ac_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="accion" value="responder_mensaje">
                                    <input type="hidden" name="mensaje_id" value="<?= (int)$msg['id'] ?>">

                                    <div class="form-group">
                                        <label for="admin-respuesta-<?= (int)$msg['id'] ?>">Tu respuesta</label>
                                        <textarea id="admin-respuesta-<?= (int)$msg['id'] ?>" name="respuesta" class="form-control" rows="4" placeholder="Escribí tu respuesta para el cliente..." required></textarea>
                                    </div>

                                    <button type="submit" class="btn-submit">Enviar respuesta</button>
                                </form>
                            </details>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">No hay mensajes de clientes por el momento.</div>
        <?php endif; ?>
    </section>

    <section class="admin-card" id="mi-perfil">
        <h2>Mi perfil</h2>
        <p>Información de la cuenta administradora de AC Alvarez Construcciones.</p>
        <div class="dashboard-list">
            <article class="dashboard-item">
                <strong>Nombre</strong>
                <p class="dashboard-muted"><?= htmlspecialchars($perfil_admin['nombre'], ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article class="dashboard-item">
                <strong>Correo electrónico</strong>
                <p class="dashboard-muted"><?= htmlspecialchars($perfil_admin['email'], ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article class="dashboard-item">
                <strong>Rol de acceso</strong>
                <p class="dashboard-muted">Administrador</p>
            </article>
        </div>
    </section>

    <section class="admin-card" id="crear-cliente">
        <h2>Registrar nuevo cliente</h2>
        <p>Creá una cuenta para que el cliente pueda ingresar al portal y consultar la información de su obra.</p>
        <form action="admin.php" method="POST" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(ac_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="accion" value="crear_cliente">
            <div class="form-group">
                <label for="cliente-nombre">Nombre completo</label>
                <input type="text" id="cliente-nombre" name="nombre" class="form-control" maxlength="150" autocomplete="name" required>
            </div>
            <div class="form-group">
                <label for="cliente-email">Correo electrónico</label>
                <input type="email" id="cliente-email" name="email" class="form-control" maxlength="254" autocomplete="email" required>
            </div>
            <div class="form-group">
                <label for="cliente-password">Contraseña inicial</label>
                <input type="password" id="cliente-password" name="password" class="form-control" minlength="12" maxlength="4096" autocomplete="new-password" required>
                <span class="upload-hint">Usá al menos 12 caracteres y compartí la contraseña inicial de forma privada con el cliente.</span>
            </div>
            <button type="submit" class="btn-submit">Crear cuenta de cliente</button>
        </form>
    </section>

<section class="card">
    <h2>Crear proyecto</h2>

    <form method="POST" action="admin.php">
        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars(ac_csrf_token(), ENT_QUOTES, 'UTF-8') ?>"
        >

        <input
            type="hidden"
            name="accion"
            value="crear_proyecto"
        >

        <label for="cliente_id">Seleccionar cliente</label>

        <select id="cliente_id" name="cliente_id" required>
            <option value="">Elegí un cliente</option>

            <?php foreach ($clientes_admin as $cliente): ?>
                <option value="<?= (int)$cliente['id'] ?>">
                    <?= htmlspecialchars(
                        $cliente['nombre'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                    -
                    <?= htmlspecialchars(
                        $cliente['email'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="nombre_proyecto">Nombre del proyecto</label>

        <input
            type="text"
            id="nombre_proyecto"
            name="nombre_proyecto"
            maxlength="200"
            required
            placeholder="Ej. Construcción de vivienda"
        >

        <button type="submit">Crear proyecto</button>
    </form>
</section>

    <section class="dashboard-section" aria-label="Indicadores generales">
        <h2 class="dashboard-section-title">Resumen general</h2>
        <div class="dashboard-grid">
            <article class="dashboard-stat">
                <div class="dashboard-stat-label">Proyectos registrados</div>
                <div class="dashboard-stat-value"><?= number_format($metricas['proyectos'], 0, ',', '.') ?></div>
                <div class="dashboard-stat-note">Obras en la base de datos</div>
            </article>
            <article class="dashboard-stat">
                <div class="dashboard-stat-label">Clientes registrados</div>
                <div class="dashboard-stat-value"><?= number_format($metricas['clientes'], 0, ',', '.') ?></div>
                <div class="dashboard-stat-note">Usuarios con rol cliente</div>
            </article>
            <article class="dashboard-stat">
                <div class="dashboard-stat-label">Fotos de avance</div>
                <div class="dashboard-stat-value"><?= number_format($metricas['fotos'], 0, ',', '.') ?></div>
                <div class="dashboard-stat-note">Fotos registradas</div>
            </article>
            <article class="dashboard-stat">
                <div class="dashboard-stat-label">Facturas y recibos</div>
                <div class="dashboard-stat-value"><?= number_format($metricas['facturas'], 0, ',', '.') ?></div>
                <div class="dashboard-stat-note">Documentos registrados</div>
            </article>
            <article class="dashboard-stat">
                <div class="dashboard-stat-label">Avance promedio</div>
                <div class="dashboard-stat-value"><?= (int)$metricas['avance_promedio'] ?>%</div>
                <div class="dashboard-progress"><span style="width: <?= max(0, min(100, $metricas['avance_promedio'])) ?>%"></span></div>
            </article>
        </div>
        <div class="dashboard-links">
            <a class="dashboard-link" href="#mi-perfil">Mi perfil</a>
            <a class="dashboard-link" href="#crear-cliente">Crear cliente</a>
            <a class="dashboard-link" href="#form-avance">Actualizar avance</a>
            <a class="dashboard-link" href="#form-presupuesto">Crear presupuesto</a>
            <a class="dashboard-link" href="#presupuestos-cargados">Gestionar presupuestos</a>
            <a class="dashboard-link" href="#form-archivo">Cargar archivo</a>
            <a class="dashboard-link" href="#archivos-cargados">Ver archivos</a>
            <a class="dashboard-link" href="#mensajes-clientes">Mensajes de clientes</a>
            <a class="dashboard-link" href="cliente.php">Gestionar clientes</a>
        </div>
    </section>

    <div class="dashboard-columns">
        <section class="dashboard-panel">
            <h2>Alertas de actividad</h2>
            <?php if ($alertas_actividad): ?>
                <div class="dashboard-list">
                    <?php foreach ($alertas_actividad as $alerta): ?>
                        <article class="dashboard-item dashboard-alert">
                            <strong><?= htmlspecialchars($alerta['nombre_proyecto'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <p class="dashboard-muted">
                                <?php if (empty($alerta['ultima_actualizacion'])): ?>
                                    Esta obra todavía no tiene archivos registrados.
                                <?php else: ?>
                                    Último archivo: <?= htmlspecialchars(date('d/m/Y H:i', strtotime($alerta['ultima_actualizacion'])), ENT_QUOTES, 'UTF-8') ?>.
                                    No hay archivos nuevos desde hace más de 14 días.
                                <?php endif; ?>
                            </p>
                            <p class="dashboard-muted">Avance registrado:
                                <?= $alerta['porcentaje_avance'] !== null ? (int)$alerta['porcentaje_avance'] . '%' : 'Sin actualizar' ?>
                            </p>
                        </article>
                    <?php endforeach; ?>
                </div>
                <p class="dashboard-muted" style="margin-top:14px">Estas alertas detectan falta de actividad documental; no determinan por sí solas si una obra está retrasada.</p>
            <?php else: ?>
                <div class="dashboard-ok">No hay proyectos que superen los 14 días sin archivos nuevos.</div>
            <?php endif; ?>
        </section>

        <section class="dashboard-panel">
            <h2>Actividad reciente</h2>
            <?php if ($actividad_reciente): ?>
                <div class="dashboard-list">
                    <?php foreach ($actividad_reciente as $actividad): ?>
                        <article class="dashboard-item">
                            <strong><?= $actividad['tipo'] === 'foto_avance' ? 'Foto de avance' : 'Factura / recibo' ?></strong>
                            <p class="dashboard-muted"><?= htmlspecialchars($actividad['nombre_proyecto'], ENT_QUOTES, 'UTF-8') ?></p>
                            <p class="dashboard-muted">Cliente: <?= htmlspecialchars($actividad['cliente'], ENT_QUOTES, 'UTF-8') ?></p>
                            <p class="dashboard-muted"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($actividad['fecha_subida'])), ENT_QUOTES, 'UTF-8') ?></p>
                            <a class="dashboard-link" href="archivo.php?id=<?= (int)$actividad['id'] ?>" target="_blank" rel="noopener noreferrer">Abrir archivo</a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="dashboard-muted">Todavía no hay actividad documental registrada.</p>
            <?php endif; ?>
        </section>
    </div>

    <section class="dashboard-section dashboard-panel">
        <h2>Resumen de obras</h2>
        <?php if ($resumen_obras): ?>
            <div class="dashboard-table-wrap">
                <table class="dashboard-table">
                    <thead><tr><th>Proyecto</th><th>Cliente</th><th>Avance</th><th>Gasto registrado</th><th>Última actividad</th></tr></thead>
                    <tbody>
                    <?php foreach ($resumen_obras as $obra): ?>
                        <?php $avance = $obra['porcentaje_avance'] !== null ? max(0, min(100, (int)$obra['porcentaje_avance'])) : null; ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($obra['nombre_proyecto'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                            <td><?= htmlspecialchars($obra['cliente'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= $avance !== null ? $avance . '%' : 'Sin actualizar' ?><div class="dashboard-progress"><span style="width: <?= $avance ?? 0 ?>%"></span></div></td>
                            <td><?= $obra['monto_gastado'] !== null ? 'Gs. ' . number_format((float)$obra['monto_gastado'], 0, ',', '.') : 'Sin actualizar' ?></td>
                            <td><?= !empty($obra['ultima_actualizacion']) ? htmlspecialchars(date('d/m/Y', strtotime($obra['ultima_actualizacion'])), ENT_QUOTES, 'UTF-8') : 'Sin archivos' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="dashboard-muted">No hay proyectos registrados para mostrar.</p>
        <?php endif; ?>
    </section>

    <section class="admin-card" id="form-avance">
        <h2>Actualizar avance y gasto</h2>
        <p>Estos datos se mostrarán al cliente en su panel de obra.</p>
        <form action="admin.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(ac_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="accion" value="actualizar_avance">
            <div class="form-group">
                <label for="progress-project">Proyecto / Cliente</label>
                <select id="progress-project" name="proyecto_id" class="form-control" required>
                    <?php if ($proyectos): foreach ($proyectos as $proyecto): ?>
                        <option value="<?= (int)$proyecto['id'] ?>"><?= htmlspecialchars($proyecto['cliente'] . ' - ' . $proyecto['nombre_proyecto'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; else: ?>
                        <option value="">No hay proyectos registrados</option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="monto-gastado">Monto gastado (Gs.)</label>
                <input type="number" id="monto-gastado" name="monto_gastado" class="form-control" min="0" step="1" placeholder="Ej.: 85000000" required>
            </div>
            <div class="form-group">
                <label for="descripcion-avance">Detalle del avance</label>
                <textarea id="descripcion-avance" name="descripcion_avance" class="form-control" maxlength="2000" placeholder="Ej.: Se completó la cimentación y comenzó el levantamiento de paredes."></textarea>
            </div>
            <div class="form-group">
                <label for="porcentaje-avance">Avance de obra (%)</label>
                <input type="number" id="porcentaje-avance" name="porcentaje_avance" class="form-control" min="0" max="100" step="1" placeholder="Ej.: 45" required>
            </div>
            <button type="submit" class="btn-submit" <?= !$proyectos ? 'disabled' : '' ?>>Guardar avance</button>
        </form>
    </section>

    <section class="admin-card" id="form-presupuesto">
        <h2>Crear presupuesto</h2>
        <p>Prepará un presupuesto para una obra y notificá al cliente desde su perfil.</p>
        <form action="admin.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(ac_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="accion" value="crear_presupuesto">
            <div class="form-group">
                <label for="proyecto-presupuesto">Proyecto / Cliente</label>
                <select id="proyecto-presupuesto" name="proyecto_id" class="form-control" required>
                    <?php if ($proyectos): foreach ($proyectos as $proyecto): ?>
                        <option value="<?= (int)$proyecto['id'] ?>"><?= htmlspecialchars($proyecto['cliente'] . ' - ' . $proyecto['nombre_proyecto'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; else: ?>
                        <option value="">No hay proyectos registrados</option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="titulo-presupuesto">Título</label>
                <input type="text" id="titulo-presupuesto" name="titulo_presupuesto" class="form-control" maxlength="150" placeholder="Ej.: Presupuesto de terminaciones" required>
            </div>
            <div class="form-group">
                <label for="descripcion-presupuesto">Descripción y detalles</label>
                <textarea id="descripcion-presupuesto" name="descripcion_presupuesto" class="form-control" maxlength="4000" placeholder="Materiales, trabajos incluidos y condiciones..."></textarea>
            </div>
            <div class="form-group">
                <label for="monto-presupuesto">Monto del presupuesto (Gs.)</label>
                <input type="number" id="monto-presupuesto" name="monto_presupuesto" class="form-control" min="0" step="1" placeholder="Ej.: 25000000" required>
            </div>
            <button type="submit" class="btn-submit" <?= !$proyectos ? 'disabled' : '' ?>>Crear y notificar presupuesto</button>
        </form>
    </section>

    <section class="admin-card" id="presupuestos-cargados">
        <h2>Presupuestos registrados</h2>
        <p>Consultá los presupuestos y cambiá su estado cuando sea necesario.</p>
        <?php if ($presupuestos_admin): ?>
            <div class="dashboard-table-wrap">
                <table class="dashboard-table">
                    <thead><tr><th>Presupuesto</th><th>Proyecto / Cliente</th><th>Monto</th><th>Estado</th><th>Actualizar estado</th></tr></thead>
                    <tbody>
                    <?php foreach ($presupuestos_admin as $presupuesto): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($presupuesto['titulo'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <p class="dashboard-muted"><?= nl2br(htmlspecialchars($presupuesto['descripcion'] ?? '', ENT_QUOTES, 'UTF-8')) ?></p>
                                <small>Emitido: <?= htmlspecialchars(date('d/m/Y', strtotime($presupuesto['fecha_emision'])), ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td>
                                <?= htmlspecialchars($presupuesto['nombre_proyecto'], ENT_QUOTES, 'UTF-8') ?>
                                <p class="dashboard-muted"><?= htmlspecialchars($presupuesto['cliente'], ENT_QUOTES, 'UTF-8') ?></p>
                            </td>
                            <td>Gs. <?= number_format((float)$presupuesto['monto'], 0, ',', '.') ?></td>
                            <td><?= htmlspecialchars(ucfirst($presupuesto['estado']), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <form action="admin.php" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(ac_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="accion" value="actualizar_presupuesto">
                                    <input type="hidden" name="presupuesto_id" value="<?= (int)$presupuesto['id'] ?>">
                                    <div class="form-group">
                                        <label for="estado-presupuesto-<?= (int)$presupuesto['id'] ?>">Estado</label>
                                        <select id="estado-presupuesto-<?= (int)$presupuesto['id'] ?>" name="estado_presupuesto" class="form-control" required>
                                            <?php foreach (['pendiente', 'enviado', 'aprobado', 'rechazado'] as $estado): ?>
                                                <option value="<?= $estado ?>" <?= $presupuesto['estado'] === $estado ? 'selected' : '' ?>><?= htmlspecialchars(ucfirst($estado), ENT_QUOTES, 'UTF-8') ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn-submit">Guardar estado</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="dashboard-muted">Todavía no hay presupuestos registrados.</p>
        <?php endif; ?>
    </section>

    <section class="admin-card" id="form-archivo">
        <h2>Cargar archivo de obra</h2>
        <p>Elegí el proyecto y el archivo que querés compartir.</p>
        <form action="admin.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(ac_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="accion" value="subir_archivo">
            <div class="form-group">
                <label for="proyecto-id">Proyecto / Cliente</label>
                <select id="proyecto-id" name="proyecto_id" class="form-control" required>
                    <?php if ($proyectos): foreach ($proyectos as $row): ?>
                        <option value="<?= (int)$row['id'] ?>"><?= htmlspecialchars($row['cliente'] . ' - ' . $row['nombre_proyecto'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; else: ?>
                        <option value="">No hay proyectos registrados</option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="document-type">Tipo de documento</label>
                <select id="document-type" name="tipo" class="form-control" required>
                    <option value="foto_avance">Foto de Avance</option>
                    <option value="factura">Factura / Recibo (PDF o Imagen)</option>
                </select>
            </div>
            <div class="form-group">
                <label for="document-description">Descripción / Observaciones</label>
                <textarea id="document-description" name="descripcion" class="form-control" maxlength="4000" placeholder="Ej.: avance semanal de obra o detalle del documento"></textarea>
            </div>
            <div class="form-group">
                <label for="document-file">Seleccionar archivo</label>
                <input type="file" id="document-file" name="archivo" class="form-control" accept="image/jpeg,image/png,image/webp,application/pdf" required>
                <span class="upload-hint">JPG, PNG, WEBP o PDF. Tamaño máximo: 10 MB.</span>
            </div>
            <button type="submit" class="btn-submit" <?= !$proyectos ? 'disabled' : '' ?>>Subir y Registrar Archivo</button>
        </form>
    </section>

    <section class="admin-files" id="archivos-cargados" aria-labelledby="admin-files-title">
        <div class="admin-files-heading">
            <h2 id="admin-files-title">Archivos cargados</h2>
            <span class="admin-files-badge"><?= count($archivos_admin) ?> en total</span>
        </div>
        <?php if ($archivos_admin): ?>
            <div class="admin-file-list">
                <?php foreach ($archivos_admin as $archivo): ?>
                    <article class="admin-file-row">
                        <div class="admin-file-top">
                            <div class="admin-file-title-group">
                                <strong class="admin-file-type"><?= $archivo['tipo'] === 'foto_avance' ? 'Foto de avance' : 'Factura / recibo' ?></strong>
                                <span class="admin-file-project"><?= htmlspecialchars($archivo['cliente'] . ' - ' . $archivo['nombre_proyecto'], ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <a class="admin-file-link" href="archivo.php?id=<?= (int)$archivo['id'] ?>" target="_blank" rel="noopener noreferrer">Ver archivo</a>
                        </div>
                        <div class="admin-file-meta-group">
                            <p class="admin-file-meta">Cargado <?= htmlspecialchars(date('d/m/Y H:i', strtotime($archivo['fecha_subida'])), ENT_QUOTES, 'UTF-8') ?></p>
                            <p class="admin-file-description"><?= trim((string)$archivo['descripcion']) !== '' ? htmlspecialchars($archivo['descripcion'], ENT_QUOTES, 'UTF-8') : 'Sin descripción' ?></p>
                        </div>
                        <details class="admin-file-edit">
                            <summary>Editar archivo</summary>
                            <div class="admin-file-edit-panel">
                                <form action="admin.php" method="POST" enctype="multipart/form-data" class="admin-file-edit-form">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(ac_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="accion" value="editar_archivo">
                                    <input type="hidden" name="archivo_id" value="<?= (int)$archivo['id'] ?>">
                                    <div class="form-group">
                                        <label for="proyecto-archivo-<?= (int)$archivo['id'] ?>">Proyecto / Cliente</label>
                                        <select id="proyecto-archivo-<?= (int)$archivo['id'] ?>" name="proyecto_id" class="form-control" required>
                                            <?php foreach ($proyectos as $proyecto): ?>
                                                <option value="<?= (int)$proyecto['id'] ?>" <?= (int)$proyecto['id'] === (int)$archivo['proyecto_id'] ? 'selected' : '' ?>><?= htmlspecialchars($proyecto['cliente'] . ' - ' . $proyecto['nombre_proyecto'], ENT_QUOTES, 'UTF-8') ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="tipo-archivo-<?= (int)$archivo['id'] ?>">Tipo de archivo</label>
                                        <select id="tipo-archivo-<?= (int)$archivo['id'] ?>" name="tipo" class="form-control" required>
                                            <option value="foto_avance" <?= $archivo['tipo'] === 'foto_avance' ? 'selected' : '' ?>>Foto de avance</option>
                                            <option value="factura" <?= $archivo['tipo'] === 'factura' ? 'selected' : '' ?>>Factura / recibo</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="descripcion-archivo-<?= (int)$archivo['id'] ?>">Descripción / Observaciones</label>
                                        <textarea id="descripcion-archivo-<?= (int)$archivo['id'] ?>" name="descripcion" class="form-control" maxlength="4000"><?= htmlspecialchars($archivo['descripcion'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="reemplazo-archivo-<?= (int)$archivo['id'] ?>">Reemplazar archivo (opcional)</label>
                                        <input type="file" id="reemplazo-archivo-<?= (int)$archivo['id'] ?>" name="archivo" class="form-control" accept="image/jpeg,image/png,image/webp,application/pdf">
                                        <span class="upload-hint">Si no seleccionás uno nuevo, se conserva el archivo actual.</span>
                                    </div>
                                    <button type="submit" class="btn-submit">Guardar cambios</button>
                                </form>
                            </div>
                        </details>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">Todavía no hay archivos cargados.</div>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
