$(function(){
	// realiza o efeito de exibir e esconder os detalhes do produto
	// ao clicar em um dos itens da lista, os outros sao fechados
	$("#operations-list-container button").on('click',function(ev){
		$(this).siblings().find(".extra-content").hide(500);
		$(this).find(".extra-content").toggle(500);
	});
});