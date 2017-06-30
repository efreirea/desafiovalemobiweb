$(function(){
	
	setUpValidation(); //inicializa o plugin de validacao
	
	//faz com que a div com mensagens de status seja reinicializada toda vez que clica no botao de submit
	$("#submit-button-id").click(function(){
		$("#result-message").empty().removeClass();
	});

	let productsCache = {}; //armazena localmente os dados de mercadorias que ja foram recuperadas do servidor, para nao precisar pegar novamente
	
	/*
	 * implementa logica de preencher automaticamente os dados do produto ao selecionar um codigo de produto
	 */
	$("#dummy-productcode-id").on('change',function(){
		let productId = $(this).val();
		if(productId==""){ //se esta adicionando um novo
			$("#product-info").find("input").val(""); //limpa os campos do produto
			$("#product-info").find("input").prop("readonly",false); // permite que sejam editados
		}else{
			$("#product-info").find("input").val(""); //limpa os camps do produto para a insercao de novos
			$("#product-info").find("input").prop("readonly",true); // impede a edicao dos campos de produto

			if(productId in productsCache){ //se a mercadoria ja foi recuperada do servidor anteriormente, entao usa a cache
				setProductInfo(productsCache[productId]);
			}else{
				/*
				 * Bloqueia a selecao de um novo produto enquanto esta buscando dados do servidor.
				 * Caso ocorra lentidao, o usuario teria a chance de trocar o produto, mas a requisicao ao servidor
				 * ainda estaria rodando. Isso poderia causar inconsistencia
				 */
				$(this).prop("disabled",true); 
				$.ajax({
					url: "/api/mercadorias/"+productId,
					method: "GET",
					dataType: "json",
					contentType: "application/json",
				}).done(function(data,status){
					productsCache[productId] = data.mercadoria; //salva na cache
					setProductInfo(productsCache[productId]);
					$("#dummy-productcode-id").prop("disabled",false); //desbloqueia a selecao de outro produto
				}).fail(function(data,status){
					$("#dummy-productcode-id").prop("disabled",false); //desbloqueia a selecao de outro produto
				});
			}

			
		}
	});
});

function setUpValidation(){
	$("form").validate({
		rules:{

			tipo_transacao: {
				required : true,
				maxlength: 1,
				pattern : "[VC]"
			},
			cod_produto : {
				required :true,
				min : 0,
				max : 9223372036854775807
			},
			name_produto: {
				required : true,
				maxlength: 100
			},
			tipo_produto :  {
				required : true,
				maxlength : 100
			},
			qnt : {
				required :true,
				min: 0,
				max: 9223372036854775807
			},
			preco :{
				required : true,
				max : 92233720368547758.07,
				pattern: "[0-9]*(\.[0-9][0-9])?"
			}
		},
		messages: {
			tipo_transacao: {
				required : "Este campo é obrigatório.",
				maxlength: "Tamanho máximo de 1 caracter.",
				pattern : "Valor Inválido. Os valores Permitidos são: V, C."
			},
			cod_produto : {
				required : "Este campo é obrigatório.",
				min : "Códigos negativos não são permitidos",
				max : "Valor máximo excedido"
			},
			name_produto: {
				required : "Este campo é obrigatório.",
				maxlength: "Nome deve conter no máximo 100 caracteres"
			},
			tipo_produto :  {
				required : "Este campo é obrigatório.",
				maxlength : "Tipo deve conter no máximo 100 caracteres."
			},
			qnt : {
				required :"Este campo é obrigatório.",
				min: "Quantidade deve ser maior ou igual a zero",
				max: "Valor máximo excedido"
			},
			preco :{
				required : "Este campo é obrigatório.",
				max : "Por favor, entre com um valor numérico menor que 92233720368547758",
				pattern: "Formato válidos: 99999 ou 9999.99 (com exatamente duas casas decimais) "
			}
		},
		errorClass : "form-control-danger form-control-feedback",
		highlight : function(element,errorClass,validClass){ //insere as classes do Bootstrap para a indicacao de erros
			$(element).addClass(errorClass).removeClass(validClass);
			$(element).parent().addClass("has-danger");
		},
		unhighlight: function(element,errorClass,validClass){ //remove as classes do Bootstrap que indicavam erro
			$(element).removeClass(errorClass).addClass(validClass);
			$(element).parent().removeClass("has-danger");
		},
		submitHandler: function(form){
			handleFormSubmission();
		}
	});
}

/**
 * Seta informacoes do produto no formulario
 *
 * @param      Object  infos   The infos
 */
function setProductInfo(infos){
	$("#productcode-id").val(infos.cod);
	$("#producttype-id").val(infos.type);
	$("#productname-id").val(infos.name);

	$("form").validate().form(); // Forca a reavaliacao de validacao do formulario para os novos valores
}

/**
 * Realiza a submissao do formulario atraves de AJAX.
 *
 * Existem dois casos diferentes de submissao
 * 
 * 1) se o produto ja existe, basta enviar requisicao do tipo POST para /operacaoes/
 * 
 * 2) se esta sendo inserido um produto novo, optou-se por realizar duas requisicoes: uma pra /mercadorias/ e outra para /operacoes/.
 * 	Dessa forma, tem-se maior controle dos erros retornados, podendo ser detectado se a mercadoria inserida possui ID unico.
 *
 * @return     {boolean}  Retorna sempre false
 */
function handleFormSubmission(){
	// ev.preventDefault();
	
	

	let dummyProdId = $("#dummy-productcode-id").val();

	let formSerialized = $("form").serializeArray();
	let formObject = {};

	for(var i=0;i<formSerialized.length;i++){// cria um objeto cujas propriedades tenham o mesmo nome que os inputs
		formObject[formSerialized[i]["name"]] = formSerialized[i]["value"];
	}
	//exibe uma mensagem de "submissao em andadamento, para o caso da requisicao demorar e o usuario perceber a mensagem"
	$("#result-message").empty().removeClass().addClass("alert alert-info").append("<p> Processando... </p>");
	if(dummyProdId==""){ // se esta inserindo novo produto
		submitProduct(formObject);
	}else{
		submitOperation(formObject);
	}



	return false;
}

/**
 * Realiza requisicao para /mercadorias/. Se for bem sucedido, chama submitOperation.
 *
 * @param      {<type>}  formObject  The form object
 */
function submitProduct(formObject){
	
	
	let localFormObject  = formObject;
	let localProductObject = {};

	//constroi um objeto cujas propriedades possuem os nomes esperados pela REST API de mercadorias
	localProductObject["cod"]=localFormObject["cod_produto"];
	localProductObject["type"]=localFormObject["tipo_produto"];
	localProductObject["name"]=localFormObject["name_produto"];
	
	$.ajax({
		url: "/api/mercadorias/",
		method: "POST",
		dataType: "json",
		contentType: "application/json",
		data: JSON.stringify(localProductObject)

	}).done(function(data, error){
		submitOperation(localFormObject); //se bem sucedido, insere operacao
	}).fail(function(data, error){
		//se erro, exibe mensagem
		$("#result-message").empty().removeClass().addClass("alert alert-danger").append("<p> Código de Produto já existente </p>");
	});
}

/**
 * realiza requisicao para /operacoes/.
 *
 * @param      {<type>}  formObject  The form object
 */
function submitOperation(formObject){
	
	$.ajax({
		url: "/api/operacoes/",
		method: "POST",
		dataType: "json",
		contentType: "application/json",
		data: JSON.stringify(formObject)

	}).done(function(data, error){
		//Se for bem sucedido, reseta o formulario e exibe mensagem
		$("form")[0].reset();
		$("#product-info").find("input").prop("readonly",false);
		$("#result-message").empty().removeClass().addClass("alert alert-success").append("<p> Formulario submetido com sucesso </p>");
	}).fail(function(data, error){
		//Se deu erro, exibe mensagem
		$("#result-message").empty().removeClass().addClass("alert alert-danger").append("<p> Erro desconhecido ao inserir operacao </p>");
	});
}