export const formatarMoeda = (numero) => {
  if(!numero) return "R$ 0,00";

  return "R$ " + numero.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}