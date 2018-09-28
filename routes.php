<?php

$app->post('/export_ead', function() {
    require __DIR__ . '/actions/ead_eksport/index.php';
});

$app->get('/make_ead', function() {
    require __DIR__ . '/actions/ead_eksport/lag_ead.php';
});

$app->get('/validate_ead', function() {
    require __DIR__ . '/actions/ead_eksport/valider_ead.php';
});

$app->get('/import', function() {
    require __DIR__ . '/actions/import/index.php';
});

$app->post('/etiketter', function() {
	require __DIR__ . '/actions/etiketter/index.php';
});

$app->get('/hent_serier', function() {
	require __DIR__ . '/actions/etiketter/hent_serier.php';
});

$app->get('/lag_etiketter', function() {
	require __DIR__ . '/actions/etiketter/etiketter.php';
});

$app->post('/saksomslag', function() {
	require __DIR__ . '/actions/saksomslag/index.php';
});

$app->get('/lag_saksomslag', function() {
	require __DIR__ . '/actions/saksomslag/get_data.php';
});