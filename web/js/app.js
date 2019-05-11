var baseurl = 'http://ajax.garapenapp.com/api';
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
'                                <label id="structure_negative" class="btn btn-lg btn-secondary">',
'                                  <input type="radio"  name="structure" value="false" autocomplete="off"><i class="fas fa-thumbs-down"></i>',
'                                </label>',
'                                <label id="structure_positive" class="btn btn-lg btn-secondary">',
'                                  <input type="radio"  name="structure" value="true" autocomplete="off"><i class="fas fa-thumbs-up"></i>',
'                                </label>',
'                        </div>  ',
'                    </div>            ',
'                    <div class="col-4">',
'                        <div class="concept">Redacción</div>',
'                        <div class="btn-group btn-group-toggle" data-toggle="buttons">',
'                                <label id="redaction_negative" class="btn btn-lg btn-secondary">',
'                                  <input type="radio"  name="redaction" value="false" autocomplete="off"><i class="fas fa-thumbs-down"></i>',
'                                </label>',
'                                <label id="redaction_positive" class="btn btn-lg btn-secondary">',
'                                  <input type="radio"  name="redaction" value="true" autocomplete="off"><i class="fas fa-thumbs-up"></i>',
'                                </label>',
'                        </div>  ',
'                    </div>            ',
'                    <div class="col-4">',
'                        <div class="concept">Implicación</div>',
'                        <div class="btn-group btn-group-toggle" data-toggle="buttons">',
'                                <label id="implication_negative" class="btn btn-lg btn-secondary">',
'                                  <input type="radio" id="implication_negative" name="implication" value="false" autocomplete="off"><i class="fas fa-thumbs-down"></i>',
'                                </label>',
'                                <label id="implication_positive" class="btn btn-lg btn-secondary">',
'                                  <input type="radio" name="implication" value="true" autocomplete="off"><i class="fas fa-thumbs-up"></i>',
'                                </label>',
'                        </div>  ',
'                    </div>    ',
'            </div>',
'            <div class="propuestas-de-mejora text-center"> ',
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
    })
});