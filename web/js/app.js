var baseurl = 'http://ajax.garapenapp.com/api';
var mysendbutton;
$(document).ready(function(){
    $("#menu-toggle").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
    });

    $.ajax({url:baseurl+'/document/active_document/'})
    .done(function(response){
        console.log(response);  
        var data = response;
        var html = '';
        html += [
            '<h1 class="document_title text-center">'+data.title+'</h1>'
        ].join('');

        var articles = data.articles;
        for(i in articles){
            var a = articles[i];
            html +=[
                '<div class="article" data-id="'+a.id+'">',
                '<img class="shield" src="images/icon-shield-check.png"/>',
                '<b class="article-title lead">'+a.title+'</b>',
                '<p class="lead text-muted">',
                    a.description,
                '</p>',
                '</div>'
            ].join('');
        }
        
        $('#main-container').html(html);

        $('.article').append([
            '<div class="votation_container">',
'            <div class="row votation text-center">',
'                    <div class="col-4">',
'                        <div class="concept">Estructura</div>',
'                        <div class="btn-group btn-group-toggle" data-toggle="buttons">',
'                                <label data-id="structure_negative" class="btn btn-lg btn-secondary">',
'                                  <input type="radio"  name="structure" value="false" autocomplete="off"><i class="fas fa-thumbs-down"></i>',
'                                </label>',
'                                <label data-id="structure_positive" class="btn btn-lg btn-secondary">',
'                                  <input type="radio"  name="structure" value="true" autocomplete="off"><i class="fas fa-thumbs-up"></i>',
'                                </label>',
'                        </div>  ',
'                    </div>            ',
'                    <div class="col-4">',
'                        <div class="concept">Redacción</div>',
'                        <div class="btn-group btn-group-toggle" data-toggle="buttons">',
'                                <label data-id="redaction_negative" class="btn btn-lg btn-secondary">',
'                                  <input type="radio"  name="redaction" value="false" autocomplete="off"><i class="fas fa-thumbs-down"></i>',
'                                </label>',
'                                <label data-id="redaction_positive" class="btn btn-lg btn-secondary">',
'                                  <input type="radio"  name="redaction" value="true" autocomplete="off"><i class="fas fa-thumbs-up"></i>',
'                                </label>',
'                        </div>  ',
'                    </div>            ',
'                    <div class="col-4">',
'                        <div class="concept">Implicación</div>',
'                        <div class="btn-group btn-group-toggle" data-toggle="buttons">',
'                                <label data-id="implication_negative" class="btn btn-lg btn-secondary">',
'                                  <input type="radio" name="implication" value="false" autocomplete="off"><i class="fas fa-thumbs-down"></i>',
'                                </label>',
'                                <label data-id="implication_positive" class="btn btn-lg btn-secondary">',
'                                  <input type="radio" name="implication" value="true" autocomplete="off"><i class="fas fa-thumbs-up"></i>',
'                                </label>',
'                        </div>  ',
'                    </div>    ',
'            </div>',
'            <div class="propuestas-de-mejora text-left"> ',
'<div class="form-group" data-id="structure_proposition">',
'<label>Propuesta de mejora para <b>Estructura</b>:</label>',
'<textarea class="form-control" rows="3"></textarea>',
'</div>           ',
'<div class="form-group" data-id="redaction_proposition">',
'<label>Propuesta de mejora para <b>Redacción</b>:</label>',
'<textarea class="form-control" rows="3"></textarea>',
'</div>           ',
'<div class="form-group" data-id="implication_proposition">',
'<label >Propuesta de mejora para <b>Implicacion</b>:</label>',
'<textarea class="form-control"  rows="3"></textarea>',
'</div>           ',
'            </div>',
'            <div class="text-center"> ',
'                        <div class="btn btn-secondary btn-lg sendvotebutton"><i class="fas fa-share-square"></i> Votar</div>',
'            </div> ',
'            </div> '
        ].join(""));
        $('.article').on('click',function(e){
            $(this).addClass('selected');
            $(this).siblings('.article').removeClass('selected');
            $(this).find('.votation_container').slideDown();
            $(this).siblings('.article').find('.votation_container').slideUp();
        });
        $('.votation label').on('click',function(e){
            var btn_id = $(this).data("id");
            var btns = btn_id.split("_");
            var proposition_form = $(this).closest('.votation').siblings('.propuestas-de-mejora').find('[data-id="'+btns[0]+'_proposition"');
            if(btns[1]=="negative"){
                proposition_form.slideDown();
                proposition_form.addClass('shown');
            }else{
                proposition_form.slideUp();
                proposition_form.removeClass('shown');
            }
            mysendbutton = $(this).parents('.article').find('.sendvotebutton');
            if($(this).parents('.article').find('.votation .btn.active').length >= 2){
                mysendbutton.addClass('active');
            }else{
                mysendbutton.removeClass('active');
            }
        });
        $('.sendvotebutton').on('click',function(e){
            if($(this).parents('.article').find('.votation .btn.active').length == 3){
            var validation_data = {
                'structure_positive':'true',
                'structure_negative':'false',
                'redaction_positive':'true',
                'redaction_negative':'false',
                'implication_positive':'true',
                'implication_negative':'false',
                'structure_proposition':'',
                'redaction_proposition':'',
                'implication_proposition':''
            };
            $(this).parent().siblings('.propuestas-de-mejora').find('.shown').each(function(index){
                var negative = $(this).data('id');
                var negativesplit = negative.split("_");
                
                validation_data[negativesplit[0]+'_negative'] = 'true';
                validation_data[negativesplit[0]+'_positive'] = 'false';

                validation_data[negativesplit[0]+'_proposition'] = $(this).find('textarea').val();

            });
            validation_data['article_id'] = $(this).parents('.article').data('id');
            console.log(validation_data);

            
            $.post({url:baseurl+'/article/vote/',data:validation_data}).done(function(response){
                $('.article[data-id="'+validation_data['article_id']+'"]').find('.votation_container').remove();
                $('.article[data-id="'+validation_data['article_id']+'"] .shield').show();
                $('.article[data-id="'+validation_data['article_id']+'"]').removeClass('selected');
            });
            

            } //if($('.votation .btn.active').length == 3){
        });
    })
});