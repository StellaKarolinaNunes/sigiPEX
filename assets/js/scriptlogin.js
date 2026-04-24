/* login_script.js */
document.addEventListener("DOMContentLoaded", function () {
  // Botão para redirecionar à tela de recuperação de senha
  const btnRecuperar = document.getElementById("btnRecuperar");
  btnRecuperar.addEventListener("click", function () {
    window.location.href = "recuperar_senha.html";
  });

  // Caso a URL contenha um parâmetro de mensagem (por exemplo, após cadastro bem-sucedido)
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has("msg")) {
    const msg = urlParams.get("msg");
    if (msg === "cadastro_sucesso") {
      alert("Cadastro realizado com sucesso! Por favor, faça login.");
    }
  }
});
