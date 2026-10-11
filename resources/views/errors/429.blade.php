@extends('errors.layout')

@section('title', __('Demasiados intentos'))
@section('code', '429')
@section('heading', __('Demasiados intentos'))
@section('text', __('Enviaste varios pedidos seguidos. Esperá un minuto y probá de nuevo.'))
