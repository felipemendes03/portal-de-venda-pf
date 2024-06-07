
export const formatarData = (dateString) => {
    if(!dateString) return "";
    
    const [data, hora] = dateString.split(' ');
    const [ano, mes, dia] = data.split('-');

  return `${dia}/${mes}/${ano}` + (hora ? " " + hora : "");
}
