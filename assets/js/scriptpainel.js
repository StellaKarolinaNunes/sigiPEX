document.addEventListener("DOMContentLoaded", function () {
  console.log("🏁 Script inicializado!");

  // 1. CARREGAMENTO DO NOME (PRIORIDADE TOTAL)
  async function carregarDados() {
    const nomeElement = document.getElementById("professorNome");
    console.log("🔍 Buscando dados do professor...");

    try {
      const response = await fetch("painel.php?v=" + new Date().getTime());
      const data = await response.json();
      console.log("✅ Resposta recebida:", data);

      if (data.error) {
        console.error("❌ Erro na sessão:", data.error);
        if (nomeElement) nomeElement.textContent = data.error;
        return;
      }

      if (nomeElement) {
        nomeElement.textContent = data.nome_professor || "Professor";
        console.log("👤 Nome exibido na Navbar: " + data.nome_professor);
      }

      // Atualiza o botão no RODAPÉ também
      const footerLogin = document.querySelector(".footer-login");
      if (footerLogin && data.nome_professor) {
        footerLogin.textContent = "Olá, " + data.nome_professor.split(" ")[0];
        footerLogin.style.background = "#1a8e4c";
        footerLogin.style.color = "white";
        footerLogin.href = "#"; // Já está logado
      }

      // Preenche campos de Perfil (Aba Meus Dados)
      const perfilCampos = {
        "conf_nome": data.nome_professor,
        "conf_siape": data.siape,
        "conf_campus": data.campus,
        "conf_telefone": data.telefone,
        "conf_email": data.email
      };
      for (let id in perfilCampos) {
        let el = document.getElementById(id);
        if (el) el.value = perfilCampos[id] || "";
      }

      // 1.1 LOGICA EXCLUSIVA ADM
      const menuAdmin = document.getElementById("menuAdminUsuarios");
      if (data.is_admin && menuAdmin) {
        menuAdmin.style.display = "block";
        
        const listaUsu = document.getElementById("listaUsuarios");
        if (listaUsu && data.usuarios) {
          listaUsu.innerHTML = data.usuarios.map(u => `
            <div class="project-card" style="padding: 25px; border-left: 5px solid #1a8e4c;">
              <h3 style="font-size: 1.1rem; color: #1e293b; margin-bottom: 10px;">${u.nome_professor}</h3>
              <p style="font-size: 0.85rem; color: #64748b;"><strong>SIAPE:</strong> ${u.siape}</p>
              <p style="font-size: 0.85rem; color: #64748b;"><strong>Campus:</strong> ${u.campus}</p>
              <p style="font-size: 0.85rem; color: #1a8e4c; font-weight: 600; margin-top: 5px;">${u.email}</p>
            </div>
          `).join('');
        }
      }

      // Renderiza Projetos em Cards
      const gridExtensao = document.getElementById("gridExtensao");
      const gridPesquisa = document.getElementById("gridPesquisa");

      if (gridExtensao && gridPesquisa && data.projetos) {
        gridExtensao.innerHTML = "";
        gridPesquisa.innerHTML = "";

        const extensaoProjs = data.projetos.filter(p => p.categoria_projeto === "Extensão");
        const pesquisaProjs = data.projetos.filter(p => p.categoria_projeto === "Pesquisa");

        const renderGrid = (container, projs) => {
          if (projs.length === 0) {
            container.innerHTML = '<p style="color: #94a3b8; font-size: 0.9rem;">Nenhum projeto encontrado.</p>';
            return;
          }
          container.innerHTML = projs.map(proj => `
            <div class="project-card" style="position: relative;">
              <div class="card-controls" style="position: absolute; top: 15px; right: 15px; display: flex; gap: 10px;">
                <button class="control-btn btn-edit" title="Editar" onclick="abrirEdicao('${proj.codigo_projeto}')" style="background: none; border: none; font-size: 1.2rem; color: #1a8e4c; cursor: pointer;"><ion-icon name="create-outline"></ion-icon></button>
                <button class="control-btn btn-delete" title="Excluir" onclick="excluirProjeto('${proj.codigo_projeto}')" style="background: none; border: none; font-size: 1.2rem; color: #e74c3c; cursor: pointer;"><ion-icon name="trash-outline"></ion-icon></button>
              </div>
              <div class="proj-lang">${proj.linguagem_projeto || "Geral"}</div>
              <h3 class="proj-title">${proj.nome_projeto}</h3>
              <p class="proj-summary">${proj.resumo_projeto}</p>
              
              <div class="proj-team">
                <strong>Autor/Siape:</strong> ${proj.siape_professor} <br>
                <strong>Resumo:</strong> ${proj.resumo_projeto.substring(0, 80)}...
              </div>

              <a href="#" class="saiba-mais" onclick="abrirEdicao('${proj.codigo_projeto}')">Editar Projeto <ion-icon name="chevron-forward-outline"></ion-icon></a>
            </div>
          `).join('');
        };

        renderGrid(gridExtensao, extensaoProjs);
        renderGrid(gridPesquisa, pesquisaProjs);
      }
    } catch (err) {
      console.error("🚨 Falha crítica no fetch:", err);
      if (nomeElement) nomeElement.textContent = "Erro de conexão";
    }
  }

  // Chama imediatamente
  carregarDados();

  // 2. LÓGICA DE ABAS (AJUSTADA)
  const tabBtns = document.querySelectorAll(".tab-btn");
  const tabContents = document.querySelectorAll(".tab-content");

  tabBtns.forEach(btn => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      const target = this.getAttribute("data-tab");

      tabContents.forEach(content => content.classList.remove("active"));
      const targetEl = document.getElementById(target);
      if (targetEl) targetEl.classList.add("active");
    });
  });

  // 3. EFEITO DE FOCO (BLUR) NO MENU
  const userWrapper = document.querySelector(".user-menu-wrapper");
  const blurOverlay = document.getElementById("blurOverlay");

  if (userWrapper && blurOverlay) {
    userWrapper.addEventListener("mouseenter", () => {
      blurOverlay.style.opacity = "1";
    });
    userWrapper.addEventListener("mouseleave", () => {
      blurOverlay.style.opacity = "0";
    });
  }

  // 4. LÓGICA DE SUBMISSÃO E DINAMISMO DO PROJETO
  const projetoForm = document.getElementById("projetoForm");
  const uploadTrigger = document.getElementById("uploadTrigger");
  const imgInput = document.getElementById("imgInput");
  const fileInput = document.getElementById("fileInput");
  const fileList = document.getElementById("fileList");

  // Adicionar Linguagens
  document.getElementById("addLinguagem").addEventListener("click", () => {
    const container = document.getElementById("linguagemContainer");
    const input = document.createElement("input");
    input.type = "text";
    input.name = "linguagens[]";
    input.className = "form-input";
    input.placeholder = "Outra linguagem...";
    container.appendChild(input);
  });

  // Adicionar Coorientadores
  document.getElementById("addCoorientador").addEventListener("click", () => {
    const container = document.getElementById("coorientadorContainer");
    const input = document.createElement("input");
    input.type = "text";
    input.name = "coorientadores[]";
    input.className = "form-input";
    input.placeholder = "Nome do outro coorientador...";
    container.appendChild(input);
  });

  // Seletor de Categoria (Extensão vs Pesquisa)
  const catBtns = document.querySelectorAll(".cat-btn");
  const categoriaInput = document.getElementById("categoriaInput");
  catBtns.forEach(btn => {
    btn.addEventListener("click", () => {
      catBtns.forEach(b => b.classList.remove("active"));
      btn.classList.add("active");
      categoriaInput.value = btn.dataset.value;
    });
  });

  // Seletor de Documentação (Galeria vs PDF)
  const docBtns = document.querySelectorAll(".toggle-btn");
  const docType = document.getElementById("docType");
  docBtns.forEach(btn => {
    btn.addEventListener("click", () => {
      docBtns.forEach(b => {
        b.classList.remove("active");
        const icon = b.querySelector("ion-icon");
        if (icon) icon.remove();
      });
      btn.classList.add("active");
      btn.insertAdjacentHTML("afterbegin", '<ion-icon name="checkmark-circle"></ion-icon> ');
      docType.value = btn.dataset.type;
      fileList.innerHTML = ""; // Limpa lista ao trocar tipo
    });
  });

  if (uploadTrigger) {
    uploadTrigger.addEventListener("click", () => {
      if (docType.value === "galeria") {
        imgInput.click();
      } else {
        fileInput.click();
      }
    });
  }

  // Feedback de Arquivos Selecionados
  [imgInput, fileInput].forEach(input => {
    input.addEventListener("change", () => {
      fileList.innerHTML = `<p><strong>${input.files.length} arquivo(s) selecionado(s):</strong></p>`;
      Array.from(input.files).forEach(f => {
        fileList.innerHTML += `<span>• ${f.name}</span><br>`;
      });
    });
  });

  // 3. LOGICA DE TROCA DE ABAS (DEEP LINKING)
  const urlParams = new URLSearchParams(window.location.search);
  const targetTab = urlParams.get('tab');

  if (targetTab) {
    const tabContents = document.querySelectorAll(".tab-content");
    const tabButtons = document.querySelectorAll(".tab-btn");

    // Remove destaque de tudo
    tabContents.forEach(c => c.classList.remove("active"));
    tabButtons.forEach(b => b.classList.remove("active"));

    // Ativa a aba alvo
    const targetEl = document.getElementById(targetTab);
    if (targetEl) {
      console.log("🎯 Ativando aba por URL:", targetTab);
      targetEl.classList.add("active");
      
      // Destaca todos os botões que apontam para essa aba (header/sidebar)
      const relatedButtons = document.querySelectorAll(`.tab-btn[data-tab="${targetTab}"]`);
      relatedButtons.forEach(b => b.classList.add("active"));
    }
  }

  // 5. MODO DE EDIÇÃO (ROBUSTEZ MÁXIMA)
  async function verificarModoEdicao() {
    console.log("🔍 Verificando parâmetros de URL...");
    const urlParams = new URLSearchParams(window.location.search);
    const codigoEdit = urlParams.get('edit');

    if (!codigoEdit) {
      console.log("ℹ️ Nenhum código de edição encontrado.");
      return;
    }

    console.log("🛠️ Tentando carregar projeto para edição:", codigoEdit);
    
    // Ativar Abas Visuais
    tabContents.forEach(c => c.classList.remove("active"));
    tabBtns.forEach(b => b.classList.remove("active"));
    
    const targetCriar = document.getElementById("criarProjetos");
    if(targetCriar) targetCriar.classList.add("active");
    const targetBtn = document.querySelector('[data-tab="criarProjetos"]');
    if(targetBtn) targetBtn.classList.add("active");

    try {
      const response = await fetch(`get_projeto.php?codigo_projeto=${codigoEdit}`);
      const proj = await response.json();

      if (proj.error) {
        console.error("❌ Erro da API get_projeto:", proj.error);
        alert("Atenção: Não foi possível carregar os dados para edição. Motivo: " + proj.error);
        return;
      }

      console.log("✅ Dados do projeto recebidos! Preenchendo campos...");

      // Seletores Seguros
      const safeSet = (name, val, type = "input") => {
        const el = document.querySelector(`${type}[name="${name}"]`);
        if (el) el.value = val || "";
        else console.warn(`⚠️ Campo não encontrado: ${type}[name="${name}"]`);
      };

      safeSet("nome_projeto", proj.nome_projeto);
      safeSet("resumo_projeto", proj.resumo_projeto, "textarea");
      safeSet("orientador", proj.orientador_projeto);
      safeSet("campus", proj.campus_projeto);
      safeSet("github_link", proj.github_link);
      safeSet("codigo_turma", proj.codigo_turma);
      safeSet("situacao", proj.situacao_projeto, "select");

      // Coorientadores (Limpa e Popula)
      const cooContainer = document.getElementById("coorientadorContainer");
      if (cooContainer) {
        cooContainer.innerHTML = '<label class="form-label">COORIENTADORES</label>';
        const coos = (proj.coorientador_projeto || "").split(", ");
        coos.forEach(c => {
          if (c.trim()) {
            const input = document.createElement("input");
            input.type = "text";
            input.name = "coorientadores[]";
            input.className = "form-input";
            input.value = c;
            cooContainer.appendChild(input);
          }
        });
      }

      // Categoria (Lógica de Botões)
      if (categoriaInput) {
        categoriaInput.value = proj.categoria_projeto;
        catBtns.forEach(btn => {
          if (btn.dataset.value === proj.categoria_projeto) btn.classList.add("active");
          else btn.classList.remove("active");
        });
      }

      // Linguagens (Limpa e Popula)
      const langContainer = document.getElementById("linguagemContainer");
      if (langContainer) {
        langContainer.innerHTML = '<label class="form-label">LINGUAGEM UTILIZADA</label>';
        const langs = (proj.linguagem_projeto || "Geral").split(", ");
        langs.forEach((l, i) => {
          const input = document.createElement("input");
          input.type = "text";
          input.name = "linguagens[]";
          input.className = "form-input";
          input.value = l;
          langContainer.appendChild(input);
        });
      }

      // Botão de Ação VIP
      const btnSalvar = document.getElementById("btnSalvar");
      if (btnSalvar) {
        btnSalvar.textContent = "Salvar Alterações";
        btnSalvar.style.background = "#574b90"; // Roxo Edição
        btnSalvar.style.boxShadow = "0 10px 30px rgba(87, 75, 144, 0.4)";
      }
      
      // ID Secreto para o Processamento
      if (projetoForm && !document.getElementById("edit_id")) {
        const hiddenId = document.createElement("input");
        hiddenId.type = "hidden";
        hiddenId.name = "codigo_projeto";
        hiddenId.id = "edit_id";
        hiddenId.value = codigoEdit;
        projetoForm.appendChild(hiddenId);
      }

    } catch (err) {
      console.error("🚨 Falha ao preencher formulário de edição:", err);
    }
  }

  // Ativer verificação após carregamento inicial
  setTimeout(verificarModoEdicao, 100);

  if (projetoForm) {
    projetoForm.addEventListener("submit", async function (e) {
      e.preventDefault();
      const btn = document.getElementById("btnSalvar");
      const isEdit = document.getElementById("edit_id");
      
      btn.textContent = isEdit ? "Salvando Alterações..." : "Gravando no Banco...";
      btn.disabled = true;

      const formData = new FormData(this);
      const urlDestino = isEdit ? "editar_projeto.php" : "criar_projeto.php";

      try {
        const response = await fetch(urlDestino, {
          method: "POST",
          body: formData
        });
        const result = await response.json();

        if (result.error) {
          alert("Erro: " + result.error);
          btn.textContent = isEdit ? "Salvar Alterações" : "Submeter Projeto";
          btn.disabled = false;
        } else {
          alert(" Sucesso! " + result.message);
          window.location.href = result.redirect || "painel.html?tab=projetos";
        }
      } catch (err) {
        console.error("Erro na submissão:", err);
        alert("Falha ao conectar com o servidor.");
        btn.textContent = isEdit ? "Salvar Alterações" : "Submeter Projeto";
        btn.disabled = false;
      }
    });
  }

  // 6. LOGOUT
  const logoutBtn = document.getElementById("logoutBtn");
  if (logoutBtn) {
    logoutBtn.addEventListener("click", function (e) {
      e.preventDefault();
      if (confirm("Deseja realmente sair?")) {
        window.location.href = "deslogar_usuario.php";
      }
    });
  }
  // 5. ATUALIZAR PERFIL (DADOS DO USUÁRIO)
  const formConfig = document.getElementById("formConfiguracoes");
  if (formConfig) {
    formConfig.addEventListener("submit", async (e) => {
      e.preventDefault();
      const formData = new FormData(formConfig);
      
      try {
        const response = await fetch("atualizar_perfil.php", {
          method: "POST",
          body: formData
        });
        const res = await response.json();
        
        if (res.success) {
          alert("✅ Dados atualizados com sucesso!");
          location.reload(); // Recarrega para atualizar o nome na navbar
        } else {
          alert("❌ Erro ao atualizar: " + res.error);
        }
      } catch (err) {
        alert("❌ Erro de conexão ao atualizar perfil.");
      }
    });
  }
});