<?php
include($_SERVER["DOCUMENT_ROOT"] . "/login/view/topo_login.php");
session_destroy();

?>

<link rel="canonical" href="https://www.talento.dev.br/trevoME.php?language=pt" />

<body class="hold-transition login-page">
    <div class="login-box">
        <div class="login-logo">
            <a href="#"><b>Pocket</b>Safe</a>
        </div>

        <div class="login-box-body">
            <p class="login-box-msg"><?= Traducao::t('Faça login para iniciar sua sessão'); ?></p>
            <form id="login" action="" method="post">
                <div class="form-group has-feedback">
                    <input type="email" class="form-control" name="email" id="email" placeholder="Email" value="<?php if (isset($_COOKIE['emailmoney'])) {
                                                                                                                    echo $_COOKIE['emailmoney'];
                                                                                                                } ?>">
                    <input type="hidden" class="form-control" id="language" name="language"
                        value="<?php echo $_COOKIE['language']; ?>" />
                    <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                </div>
                <div class="form-group has-feedback">
                    <input type="password" class="form-control" name="password" id="password" placeholder="Password"
                        value="<?php if (isset($_COOKIE['senhamoney'])) {
                                    echo $_COOKIE['senhamoney'];
                                }; ?>">
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>
                <div class="row">
                    <div class="col-xs-8">
                        <div class="checkbox icheck">
                            <label>
                                <input type="checkbox" <?php if (isset($_COOKIE['senhamoney'])) {
                                                            echo 'checked="checked"';
                                                        }; ?>> <?= Traducao::t('Lembre de mim'); ?>
                            </label>
                        </div>
                    </div>

                    <div class="col-xs-4">
                        <button type="submit"
                            class="btn btn-primary btn-block btn-flat"><?= Traducao::t('Entrar'); ?></button>
                    </div>

                </div>
            </form>
            <!--
			<div class="social-auth-links text-center">
				<p>- OR -</p>
				<a href="javascript:void(0)" onClick="login();" class="btn btn-block btn-social btn-facebook btn-flat"><i class="fa fa-facebook"></i> <?= Traducao::t('Entrar usando Facebook'); ?></a>
				<a href="#" class="btn btn-block btn-social btn-google btn-flat"><i class="fa fa-google-plus"></i><?= Traducao::t('Entrar usando o Google'); ?></a>
			</div>
			-->
            <p id="resposta_login" class="d-none"></p>
            <a
                href="/login/view/recuperar_senha.php?language=<?php echo $_COOKIE['language']; ?>"><?= Traducao::t('Esqueci a senha'); ?></a><br>
            <a href="/login/view/registro.php?language=<?php echo $_COOKIE['language']; ?>"
                class="text-center"><?= Traducao::t('Registrar novo usuário'); ?></a><br>
            <a href="#" onclick="backHome()"><?= Traducao::t('Sair'); ?></a>
        </div>

    </div>

</body>
<script src="<?php echo JS . 'jquery.min.js'; ?>"></script>
<script src="<?php echo JS . 'bootstrap.min.js'; ?>"></script>
<script src="<?php echo JS . 'icheck.min.js'; ?>"></script>
<script>
$(function() {
    $('input').iCheck({
        checkboxClass: 'icheckbox_square-blue',
        radioClass: 'iradio_square-blue',
        increaseArea: '20%' /* optional */
    });
});
</script>
<script>
function backHome() {

    JSReceiver.backHome();
}
</script>
<script>
//$("#loading").hide();
$("#login").submit(function(event) {
    // cancels the form submission
    event.preventDefault();
    submitForm();
});

function submitForm() {

    $("#resposta_login").html("<?= Traducao::t('Efetuando login! Aguarde....'); ?>");
    var language = "<?= $_COOKIE['language']; ?>";
    // Initiate Variables With Form Content
    var $form = $("#login");
    // let's select and cache all the fields
    var $inputs = $form.find("input, select, button, textarea");
    // serialize the data in the form
    var serializedData = $form.serialize();

    $.ajax({
        type: "POST",
        url: "/login/controller/login.php",
        data: serializedData,
        success: function(text) {

            if (text == "Sucesso") {


                $("#resposta_login").html(
                    "<?= Traducao::t('Login efetuado com sucesso! Iniciando o sistema, aguarde....'); ?>"
                );
                $("#resposta_login").removeClass('d-none');
                setTimeout(function() {

                    window.location.href = "/palco/view/principal.php";

                }, 500);


            } else {


                if (language == "en") {

                    $("#resposta_login").html(
                        "Wrong Email or Password, contact the administrator or try again!!");
                    $("#resposta_login").removeClass('d-none');

                } else if (language == "pt") {

                    $("#resposta_login").html("Email ou Password incorreto!");
                    $("#resposta_login").removeClass('d-none');

                } else {

                    $("#resposta_login").html("Email ou Password incorreto!");
                    $("#resposta_login").removeClass('d-none');
                }

            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {

            $("#resposta_login").html(textStatus);
            $("#resposta_login").removeClass('d-none');
        }
    });

}
</script>


<script>
/*
	function login(){
		FB.login(function(response){
			statusChangeCallback(response);
		});
	}
	function logOut(){
		FB.logout(function(response){
			statusChangeCallback(response);
			$("#resposta_login").html('Efetuou logOut!');
		});
	}
	function statusChangeCallback(response){

		if(response.status=== 'connected'){
			$("#resposta_login").html('Conectado');
			FB.api('/me?fields=email,name', function(response){
				$("#resposta_login").html(response.name + ',' + response.email+ ',' + response.id);
				//logOut();
			});
		}else if(response.status=== 'not_authorized'){
			$("#resposta_login").html('Não autorizado');
		}else{
			$("#resposta_login").html(response.status);
		}
	}
  window.fbAsyncInit = function() {
    FB.init({
      appId      : '628404535329000',
      cookie     : true,
      version    : 'v2.1'
    });
      
    FB.getLoginStatus(function(response) {
		statusChangeCallback(response);
	});  
      
  };

  (function(d, s, id){
     var js, fjs = d.getElementsByTagName(s)[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement(s); js.id = id;
     js.src = "https://connect.facebook.net/en_US/sdk.js";
     fjs.parentNode.insertBefore(js, fjs);
   }(document, 'script', 'facebook-jssdk'));


*/
</script>

</html>