document.addEventListener("DOMContentLoaded", function () {
  // Função para obter o parâmetro da URL
  function getQueryParam(param) {
    let params = new URLSearchParams(window.location.search);
    return params.get(param);
  }

  const codigo_projeto = getQueryParam("codigo_projeto");
  if (!codigo_projeto) {
    alert("Projeto não informado.");
    window.location.href = "painel.html";
    return;
  }

  // Carrega os dados do projeto usando get_projeto.php
  fetch("get_projeto.php?codigo_projeto=" + codigo_projeto)
  .then(response => response.json())
  .then(data => {
    console.log(data); // Veja os dados no console do navegador
    if (data.error) {
      alert(data.error);
      window.location.href = "painel.html";
      return;
    }
    // Preenche os campos do formulário
    document.getElementById("codigo_projeto").value = data.codigo_projeto;
    document.getElementById("nome_projeto").value = data.nome_projeto || "";
    document.getElementById("resumo_projeto").value = data.resumo_projeto || "";
    document.getElementById("codigo_turma").value = data.codigo_turma || "";
    
    // Preenche coorientadores – usando split com filtro para remover entradas vazias
    let coorientadores = (data.coorientador_projeto && data.coorientador_projeto.trim() !== "") 
                           ? data.coorientador_projeto.split(/\s*,\s*/).filter(item => item !== "")
                           : [];
    let coorientadoresContainer = document.getElementById("coorientadoresContainer");
    coorientadores.forEach(coor => {
      let div = document.createElement("div");
      div.className = "coorientador-field";
      div.innerHTML = `<input type="text" name="coorientadores[]" value="${coor}" required> <button type="button" class="remove-field" onclick="removeField(this)">-</button>`;
      coorientadoresContainer.appendChild(div);
    });
    
    // Preenche alunos – usando split com filtro para remover entradas vazias
    let nomesAlunos = (data.nome_aluno && data.nome_aluno.trim() !== "") 
                        ? data.nome_aluno.split(/\s*,\s*/).filter(item => item !== "")
                        : [];
    let emailsAlunos = (data.email_aluno && data.email_aluno.trim() !== "") 
                        ? data.email_aluno.split(/\s*,\s*/).filter(item => item !== "")
                        : [];
    let alunosContainer = document.getElementById("alunosContainer");
    nomesAlunos.forEach((nome, index) => {
      let email = emailsAlunos[index] || "";
      let div = document.createElement("div");
      div.className = "aluno-field";
      div.innerHTML = `<input type="text" name="alunos_nomes[]" value="${nome}" required> <input type="email" name="alunos_emails[]" value="${email}" required> <button type="button" class="remove-field" onclick="removeField(this)">-</button>`;
      alunosContainer.appendChild(div);
    });
    
    // Preenche imagens já existentes
    let imagensContainer = document.getElementById("imagensContainer");
    let dbImagens = [];
    try {
      let imagens = JSON.parse(data.imagem_projeto);
      imagens.forEach((img, index) => {
        dbImagens.push(img);
        let div = document.createElement("div");
        div.className = "image-preview";
        div.setAttribute("data-index", index);
        div.setAttribute("data-origin", "db");
        div.innerHTML = `<img src="data:image/*;base64,${img}" alt="Imagem do Projeto"><button type="button" class="remove-imagem" onclick="removeImage(${index}, true)">X</button>`;
        imagensContainer.appendChild(div);
      });
    } catch (e) {
      // nenhuma imagem existente
    }
    document.getElementById("imagens_existentes").value = JSON.stringify(dbImagens);
    
    // Preenche arquivos já existentes
    let arquivosContainer = document.getElementById("arquivosContainer");
    let dbArquivos = [];
    try {
      let arquivos = JSON.parse(data.arquivo_projeto);
      arquivos.forEach((arq, index) => {
        dbArquivos.push(arq);
        let fileName = (typeof arq === "object" && arq.name && arq.name.trim() !== "") ? arq.name : "Arquivo " + (index + 1);
        let fileData = (typeof arq === "object" && arq.data) ? arq.data : arq;
        
        let binary = atob(fileData);
        let array = [];
        for (let i = 0; i < binary.length; i++) {
          array.push(binary.charCodeAt(i));
        }
        let mimeType;
        if (/\.pdf$/i.test(fileName)) {
            mimeType = "application/pdf";
        } else if (/\.docx?$/i.test(fileName)) {
            mimeType = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
        } else if (/\.pptx?$/i.test(fileName)) {
            mimeType = "application/vnd.openxmlformats-officedocument.presentationml.presentation";
        } else {
            mimeType = "application/octet-stream";
        }
        let blob = new Blob([new Uint8Array(array)], { type: mimeType });
        let fileURL = URL.createObjectURL(blob);
        
        let div = document.createElement("div");
        div.className = "arquivo-preview";
        div.setAttribute("data-index", index);
        div.setAttribute("data-origin", "db");
        if (/\.pdf$/i.test(fileName)) {
          div.innerHTML = `<embed src="${fileURL}" type="application/pdf" width="100%" height="200px"><br><span>${fileName}</span> <button type="button" class="remove-arquivo" onclick="removeArquivo(${index}, true)">X</button>`;
        } else {
          div.innerHTML = `<a href="${fileURL}" target="_blank" download="${fileName}">Visualizar ${fileName}</a> <button type="button" class="remove-arquivo" onclick="removeArquivo(${index}, true)">X</button>`;
        }
        arquivosContainer.appendChild(div);
      });
    } catch (e) {
      // nenhum arquivo existente
    }
    document.getElementById("arquivos_existentes").value = JSON.stringify(dbArquivos);
  })
  .catch(() => {
    alert("Erro ao carregar os dados do projeto.");
    window.location.href = "painel.html";
  });

  // O restante do código permanece inalterado
  window.removeField = function(btn) {
    btn.parentElement.remove();
  };

  document.getElementById("addCoorientador").addEventListener("click", function() {
    let container = document.getElementById("coorientadoresContainer");
    let div = document.createElement("div");
    div.className = "coorientador-field";
    div.innerHTML = '<input type="text" name="coorientadores[]" placeholder="Coorientador" required> <button type="button" class="remove-field" onclick="removeField(this)">-</button>';
    container.appendChild(div);
  });

  document.getElementById("addAluno").addEventListener("click", function() {
    let container = document.getElementById("alunosContainer");
    let div = document.createElement("div");
    div.className = "aluno-field";
    div.innerHTML = '<input type="text" name="alunos_nomes[]" placeholder="Nome do Aluno" required> <input type="email" name="alunos_emails[]" placeholder="E-mail do Aluno" required> <button type="button" class="remove-field" onclick="removeField(this)">-</button>';
    container.appendChild(div);
  });

  window.selectedImages = window.selectedImages || [];
  document.getElementById("addImagem").addEventListener("click", function() {
    document.getElementById("imagemInput").click();
  });
  document.getElementById("imagemInput").addEventListener("change", function(event) {
    let files = event.target.files;
    let container = document.getElementById("imagensContainer");
    let currentCount = container.querySelectorAll(".image-preview").length;
    if (currentCount + files.length > 3) {
      alert("Você pode adicionar no máximo 3 imagens.");
      return;
    }
    for (let i = 0; i < files.length; i++) {
      let file = files[i];
      if (!/\.(jpg|jpeg|png)$/i.test(file.name)) {
        alert("Tipo de arquivo inválido para imagem: " + file.name);
        continue;
      }
      window.selectedImages.push(file);
      let index = window.selectedImages.length - 1;
      let reader = new FileReader();
      reader.onload = function(e) {
        let div = document.createElement("div");
        div.className = "image-preview";
        div.setAttribute("data-index", index);
        div.innerHTML = '<img src="' + e.target.result + '" alt="Imagem do Projeto"><button type="button" class="remove-imagem" onclick="removeImage(' + index + ')">X</button>';
        container.appendChild(div);
      };
      reader.readAsDataURL(file);
    }
    event.target.value = "";
  });
  window.removeImage = function(index, isDB = false) {
    let container = document.getElementById("imagensContainer");
    let preview = container.querySelector('[data-index="'+ index +'"]');
    if (preview) preview.remove();
    if (isDB) {
      let imagensExistentes = JSON.parse(document.getElementById("imagens_existentes").value || "[]");
      imagensExistentes[index] = null;
      imagensExistentes = imagensExistentes.filter(item => item !== null);
      document.getElementById("imagens_existentes").value = JSON.stringify(imagensExistentes);
    } else {
      window.selectedImages[index] = null;
    }
  };

  window.selectedArquivos = window.selectedArquivos || [];
  document.getElementById("addArquivo").addEventListener("click", function() {
    document.getElementById("arquivoInput").click();
  });
  document.getElementById("arquivoInput").addEventListener("change", function(event) {
    let files = event.target.files;
    let container = document.getElementById("arquivosContainer");
    for (let i = 0; i < files.length; i++) {
      let file = files[i];
      if (!/\.(doc|docx|ppt|pptx|pdf)$/i.test(file.name)) {
        alert("Tipo de arquivo inválido para arquivo do projeto: " + file.name);
        continue;
      }
      window.selectedArquivos.push(file);
      let index = window.selectedArquivos.length - 1;
      let fileURL = URL.createObjectURL(file);
      let div = document.createElement("div");
      div.className = "arquivo-preview";
      div.setAttribute("data-index", index);
      if (/\.pdf$/i.test(file.name)) {
        div.innerHTML = `<embed src="${fileURL}" type="application/pdf" width="100%" height="200px"><br><span>${file.name}</span> <button type="button" class="remove-arquivo" onclick="removeArquivo(${index})">X</button>`;
      } else {
        div.innerHTML = `<a href="${fileURL}" target="_blank" download="${file.name}">Visualizar ${file.name}</a> <button type="button" class="remove-arquivo" onclick="removeArquivo(${index})">X</button>`;
      }
      container.appendChild(div);
    }
    event.target.value = "";
  });
  window.removeArquivo = function(index, isDB = false) {
    let container = document.getElementById("arquivosContainer");
    let preview = container.querySelector('[data-index="'+ index +'"]');
    if (preview) preview.remove();
    if (isDB) {
      let arquivosExistentes = JSON.parse(document.getElementById("arquivos_existentes").value || "[]");
      arquivosExistentes[index] = null;
      arquivosExistentes = arquivosExistentes.filter(item => item !== null);
      document.getElementById("arquivos_existentes").value = JSON.stringify(arquivosExistentes);
    } else {
      window.selectedArquivos[index] = null;
    }
  };

  document.getElementById("editarProjetoForm").addEventListener("submit", function (e) {
    e.preventDefault();
    let formData = new FormData(this);

    let imagensExistentes = JSON.parse(document.getElementById("imagens_existentes").value || "[]");
    imagensExistentes.forEach((img) => {
      formData.append("imagem_projeto_existente[]", img);
    });

    let arquivosExistentes = JSON.parse(document.getElementById("arquivos_existentes").value || "[]");
    arquivosExistentes.forEach((arq) => {
      formData.append("arquivo_projeto_existente[]", arq);
    });

    window.selectedImages.forEach(file => {
      if (file) {
        formData.append("imagem_projeto[]", file);
      }
    });

    window.selectedArquivos.forEach(file => {
      if (file) {
        formData.append("arquivo_projeto[]", file);
      }
    });

    fetch("editar_projeto.php", {
      method: "POST",
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      let message = document.getElementById("editarProjetoMessage");
      if (data.error) {
        message.textContent = data.error;
        message.style.color = "red";
        alert("Erro: " + data.error);
      } else {
        message.textContent = data.message;
        message.style.color = "green";
        alert("Sucesso: " + data.message);
        window.location.href = "painel.html";
      }
    })
    .catch(() => {
      alert("Erro ao atualizar o projeto.");
    });
  });

  document.getElementById("voltarBtn").addEventListener("click", function () {
    window.location.href = "painel.html";
  });
});
