<?php
/**
 * 路由配置文件
 * 
 * 在这里定义应用的所有路由规则
 */

// 首页路由
$router->get('/', 'HomeController@index');

// 问诊相关路由
$router->get('/consultation', 'ConsultationController@index');
$router->get('/consultation/form', 'ConsultationController@form');
$router->post('/consultation/submit', 'ConsultationController@submit');
$router->post('/consultation/saveDraft', 'ConsultationController@saveDraft');
$router->get('/consultation/result/{id}', 'ConsultationController@result');
$router->get('/consultation/check-status/{id}', 'ConsultationController@checkStatus');
$router->get('/consultation/export/{id}', 'ConsultationController@exportPdf');
$router->get('/consultation/continue/{id}', 'ConsultationController@continueConsultation');
$router->get('/consultation/pdf/{id}', 'ConsultationController@exportPdf');

// 患者相关路由
$router->get('/patients', 'PatientController@index');
$router->get('/patients/{id}', 'PatientController@show');
$router->post('/patients/create', 'PatientController@create');

// 历史记录路由
$router->get('/history', 'HistoryController@index');
$router->get('/history/detail/{id}', 'HistoryController@detail');
$router->get('/history/patient/{id}', 'HistoryController@patient');
$router->post('/history/delete', 'HistoryController@delete');
$router->get('/history/export-json', 'HistoryController@exportJson');
$router->get('/history/export-csv', 'HistoryController@exportCsv');
$router->get('/history/export-excel', 'HistoryController@exportExcel');
$router->get('/history/export-patients', 'HistoryController@exportPatients');
$router->get('/history/export-detail/{id}', 'HistoryController@exportDetail');

// API 路由
$router->post('/api/diagnosis', 'ApiController@diagnosis');
$router->get('/api/export/{id}', 'ApiController@export'); 