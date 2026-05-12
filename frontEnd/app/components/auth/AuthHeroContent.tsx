export function LoginHeroSummary() {
  return (
    <div className="mt-8 rounded-lg border border-white/15 bg-white/10 p-6 shadow-2xl shadow-blue-950/30 backdrop-blur">
      <div className="mb-6 flex items-start justify-between">
        <div>
          <p className="text-sm text-blue-100">Saldo previsto</p>
          <p className="mt-1 text-3xl font-semibold">R$ 8.420,00</p>
        </div>
        <div className="rounded-lg bg-emerald-400/15 px-3 py-2 text-sm font-semibold text-emerald-200">
          +12,4%
        </div>
      </div>

      <div className="grid h-40 grid-cols-7 items-end gap-3 border-b border-white/15 pb-4">
        {[42, 56, 48, 72, 64, 88, 78].map((height, index) => (
          <div className="flex h-full items-end" key={height + index}>
            <div
              className="w-full rounded-t-md bg-blue-300"
              style={{ height: `${height}%` }}
            />
          </div>
        ))}
      </div>

      <div className="mt-5 grid grid-cols-3 gap-3 text-sm">
        <div>
          <p className="text-blue-200">Entradas</p>
          <p className="mt-1 font-semibold text-white">R$ 5.200</p>
        </div>
        <div>
          <p className="text-blue-200">Despesas</p>
          <p className="mt-1 font-semibold text-white">R$ 2.180</p>
        </div>
        <div>
          <p className="text-blue-200">Carteiras</p>
          <p className="mt-1 font-semibold text-white">3 ativas</p>
        </div>
      </div>
    </div>
  );
}

export function RegisterHeroSteps() {
  return (
    <div className="mt-8 grid gap-4">
      {[
        ["01", "Conta protegida por login e token"],
        ["02", "Carteira padrão criada no primeiro acesso"],
        ["03", "Dados preparados para relatórios financeiros"],
      ].map(([number, text]) => (
        <div className="flex items-center gap-4 rounded-lg border border-white/15 bg-white/10 p-4 backdrop-blur" key={number}>
          <span className="flex h-10 w-10 items-center justify-center rounded-md bg-blue-300 text-sm font-bold text-blue-950">
            {number}
          </span>
          <p className="text-sm font-medium text-blue-50">{text}</p>
        </div>
      ))}
    </div>
  );
}
