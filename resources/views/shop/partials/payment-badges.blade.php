<div class="mt-6 space-y-3 border-t border-zinc-100 pt-5 text-xs text-zinc-600 dark:border-ink-700 dark:text-zinc-400">
    <p class="flex items-start gap-2"><x-icon name="shield" class="size-4 shrink-0 text-emerald-600" /> {{ __('Pago seguro con Mercado Pago. Tus datos de tarjeta no pasan por nuestro sitio.') }}</p>
    <p class="flex items-start gap-2"><x-icon name="check" class="size-4 shrink-0 text-emerald-600" /> {{ __('Crédito (hasta :n cuotas), débito, dinero en cuenta o dos tarjetas a la vez.', ['n' => config('services.mercadopago.max_installments')]) }}</p>
</div>
