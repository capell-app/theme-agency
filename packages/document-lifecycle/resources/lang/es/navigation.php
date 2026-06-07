<?php

declare(strict_types=1);

return [
    'group' => 'Ciclo de documentos',
    'document' => 'documento',
    'documents' => 'Documentos controlados',
    'fields' => [
        'key' => 'Clave',
        'title' => 'Titulo',
        'status' => 'Estado',
        'metadata' => 'Metadatos',
        'content' => 'Contenido',
        'metadata_note' => 'Nota interna',
        'review_due_at' => 'Revision pendiente',
        'expires_at' => 'Caduca',
        'publications' => 'Publicaciones',
        'version' => 'Version',
        'hash' => 'Hash',
        'revision' => 'Revision',
        'published_at' => 'Publicado',
        'context' => 'Contexto',
        'acceptor' => 'Aceptante',
        'accepted_at' => 'Aceptado',
        'updated_at' => 'Actualizado',
        'all_versions' => 'Todas las versiones',
    ],
    'relations' => [
        'publications' => 'Publicaciones',
        'acceptances' => 'Aceptaciones',
    ],
    'actions' => [
        'register_document' => 'Registrar documento',
        'publish_version' => 'Publicar version',
        'record_acceptance' => 'Registrar aceptacion',
        'archive_document' => 'Archivar documento',
        'restore_document' => 'Restaurar documento',
        'export_acceptance_evidence' => 'Exportar evidencia CSV',
        'export_outstanding_acceptances' => 'Exportar pendientes CSV',
        'download_acceptance_certificate' => 'Descargar JSON firmado',
        'download_publication_diff' => 'Descargar diff JSON',
    ],
    'messages' => [
        'version_published' => 'Version del documento publicada.',
        'acceptance_recorded' => 'Aceptacion del documento registrada.',
        'document_archived' => 'Documento archivado.',
        'document_restored' => 'Documento restaurado.',
    ],
    'commands' => [
        'archive_expired' => [
            'summary' => ':count documento(s) controlado(s) caducado(s) archivado(s).',
        ],
    ],
    'portal' => [
        'accepted_status' => 'Aceptado',
        'acceptance_description' => 'Version :version aceptada.',
    ],
    'status' => [
        'draft' => 'Borrador',
        'active' => 'Activo',
        'archived' => 'Archivado',
    ],
];
