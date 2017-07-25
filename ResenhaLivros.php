<?php
 /////////////////////////
 // INSERT CUSTOM FIELDS
 /////////////////////////
?>
<?php
$idgoodreads = get_post_meta($post->ID, "idgoodreads", true);
$isbn = get_post_meta($post->ID, "isbn", true);

if ($post->ID == 27080 || empty($idgoodreads)) { 
 $goodreadskey = 'wGDmY02p6XmXoAmQRviA'; 
 $xml_string = 'http://www.goodreads.com/book/isbn?format=xml&isbn=' . $isbn . '&key=' . $goodreadskey;
 $xml= simplexml_load_file($xml_string);
 if (!empty($xml->book->title)) {    
			  delete_post_meta($post->ID, 'titulo');  
			  add_post_meta($post->ID, 'titulo', (string)$xml->book->title, true);
			  
			  if (!empty($xml->book->work->original_title)) { 
			    delete_post_meta($post->ID, 'titulooriginal'); 
			    if ((string)$xml->book->title != (string)$xml->book->work->original_title) { 
			       add_post_meta($post->ID, 'titulooriginal', (string)$xml->book->work->original_title, true);
			    }
			  }	
			  
			  $autores = $xml->xpath('book/authors/author'); 
			  if (!empty($autores)) { 
			    wp_delete_object_term_relationships( $post->ID, 'autor' );
			    foreach($autores as $autor)  { 
				  if ($autor->role == 'translator') {
				      delete_post_meta($post->ID, 'traducao');  
			              add_post_meta($post->ID, 'traducao',(string)$autor->name, true); 
				  } else {
                                      $autortaxonomy = get_term_by('name', $autor->name, 'autor');
                                      if (!$autortaxonomy) { 
                                           wp_insert_term( $autor->name, 'autor');
				           $autortaxonomy = get_term_by('name', $autor->name, 'autor'); 
                                      }  
                                      wp_set_post_terms( $post->ID, $autortaxonomy->term_id, 'autor', true);
                                  }
			      }	   
			  }
			        
			   if (!empty($xml->book->publication_year)) { 
			       delete_post_meta($post->ID, 'ano');  
			       add_post_meta($post->ID, 'ano', (string)$xml->book->publication_year, true); 
			  }	 

                           if ((string)$xml->book->num_pages != '') { 
			       delete_post_meta($post->ID, 'paginas');  
			       add_post_meta($post->ID, 'paginas', (string)$xml->book->num_pages, true); 
			  }				  
                           
			  if (!empty($xml->book->publisher)) { 
			    wp_delete_object_term_relationships( $post->ID, 'editora' );
			    $editorataxonomy = get_term_by('name', $xml->book->publisher, 'editora');
                          if (!$editorataxonomy) { 
                              wp_insert_term( $xml->book->publisher, 'editora');
			                  $editorataxonomy = get_term_by('name', $xml->book->publisher, 'editora'); 
                          }
                          wp_set_post_terms( $post->ID, $editorataxonomy->term_id, 'editora', true);
			  }
			  
			  if (!empty($xml->book->id)) { 
			       delete_post_meta($post->ID, 'idgoodreads');  
			       add_post_meta($post->ID, 'idgoodreads', (string)$xml->book->id, true);
			  }	
			  
			  get_template_part( '/Afiliados');   
  }			  
} //if ($post->ID == 21685) 
?>


<?php
 ///////////////////////////////////////////////////////////////
 // Livros da série
 ///////////////////////////////////////////////////////////////
?>
<?php
$seriesdelivros = get_the_terms($post->ID, 'series-de-livros');
$link = get_bloginfo( 'url' ) . '/series-de-livros/' . $seriesdelivros[0]->slug;
$spinoff = get_term($seriesdelivros[0]->parent, 'series-de-livros');
$linkspinoff = get_bloginfo( 'url' ) . '/series-de-livros/' . $spinoff->slug;
if (!is_wp_error($spinoff)) {
  $infospinoff = ' (spin-off de <a href="' . $linkspinoff . '">'.  $spinoff->name . '</a>)';
}

 if ($seriesdelivros != '') {
  
  echo '<ol class="livrosSerie">Livros da série <strong><a href="' . $link . '">'. $seriesdelivros[0]->name .'</a></strong>'. $infospinoff .':<br /><br />';
  foreach ($seriesdelivros as $term){
        echo $term->description;
    }         
 echo '</ol>';
 }
?>

<?php
 ///////////////////////////////////////////////////////////////
 // Resenhas de LIVROS
 ///////////////////////////////////////////////////////////////
?>
<div itemprop="review" itemscope itemtype="http://schema.org/Review">
<meta itemprop="author" content="<?php the_author(); ?>">
<?php if(!is_feed()) {
     $capa_original = get_post_meta($post->ID, "capa_original", true);
	 $livroquedeuorigem = get_post_meta($post->ID, "livroquedeuorigem", true); 
     if( $capa_original ): 
      $capaooriginal = getimagesize(get_post_meta($post->ID, "capa_original", true));
      $width = $capaooriginal[0];
      $height = $capaooriginal[1];
?>
        <div class = "livroinspiroufilme">
			 <a alt="<?php echo $livroquedeuorigem; ?>" 
			 title="<?php echo $livroquedeuorigem; ?>" 
			 href="<?php echo $capa_original; ?>"  data-rel="lightbox-1">
			 <img alt="" src="<?php echo $capa_original; ?>" width="<?php echo $width ?>px" height="<?php echo $height ?>px"/></a>
			 <p class="wp-caption-text">Capa original</p>
        </div>
     <?php endif; //if $capa_original
      } // if not is_feed
	  
        $imagem = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'medium'); 
	$titulo = get_post_meta($post->ID, "titulo", true);
	if (!empty($imagem)) :
		$largura = $imagem[1];
		$altura = $imagem[2]; 
	?>
    <div id="posterserie"><span class="posterserieimg"><img src="<?php echo $imagem[0]; ?>" alt="<?php echo $titulo; ?>" title="<?php echo $titulo; ?>" width="<?php echo $largura; ?>px" height="<?php echo $altura; ?>px"  ></span>
 <br />

<div class="fichatecnica">
<?php
 // INFORMAÇÕES EM TEXTO
?>
<p class="fichatecnicain">
<?php 
 // CLASSIFICAÇÃO
?>
<?php 
   $estrelas = get_post_meta($post->ID,'classificacao',true); 
   $urlestrela = str_replace(' ', '-', $estrelas);
   if(has_tag('livro favorito', $post->ID)) {
      $classestrelas = 'fa fa-heart';
      $class = 'classificacaofavorito';
   } else {
      $classestrelas = 'fa fa-star';
      $class = 'classificacao';
   } 	  
   if ($estrelas) { ?>
       <span itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">
       <span class="classifititulo">Classificação:</span> 
       <span class="<?php echo $class; ?>">
         <a href="http://www.estantelotada.com.br/tag/<?php echo $urlestrela; ?>" alt="<?php echo $estrelas; ?>" 
          title="<?php echo $estrelas; ?>">
			<?php 
			  $i = 1;                
			  while ($i <= $estrelas[0]) { ?>
				 <span class="<?php echo $classestrelas; ?>"></span>
			<?php 
			  $i++; 
			  } 

			  $j = 1;     
			  $estrelasrestantes = 5 - $estrelas[0];             
			  while ($j <= $estrelasrestantes) { ?>
				 <span class="fa fa-star-o"></span>
			<?php 
			  $j++; 
			  } 
             ?>
          </a><br />
	  <meta itemprop="ratingValue" content="<?php echo $estrelas[0] ?>" />
	  <meta itemprop="bestRating" content="5" />
</span>
<?php } ?>


<?php
 // DADOS TÉCNICOS
?>
  <?php 
  // $titulo = get_post_meta($post->ID, "titulo", true);
   $titulooriginal = get_post_meta($post->ID, "titulooriginal", true);
   $generos = get_the_terms( $post->ID, 'genero' );
   $autores = get_the_terms( $post->ID, 'autor' );
   $ano = get_post_meta($post->ID, "ano", true);
   $paginas = get_post_meta($post->ID, "paginas", true);
   $editoras = get_the_terms( $post->ID, 'editora' );
   $traducao = get_post_meta($post->ID, "traducao", true);
   $niveldificuldade = get_post_meta($post->ID, "niveldificuldade", true);
   $lancamentonobrasil = get_post_meta($post->ID, "lancamentonobrasil", true);
   $recebido = get_post_meta($post->ID,'recebidon',true);
   
     if( $titulo ): ?>
        <span itemprop="itemReviewed" itemscope itemtype="http://schema.org/Thing">
        <span itemprop="name"><strong><?php echo $titulo; ?></strong></span></span>
      <?php 
      if ($idgoodreads) { 
      $endereco = 'https://www.goodreads.com/book/show/'. $idgoodreads; ?>
        <span class="goodreads"><a href="<?php echo $endereco; ?>" rel="nofollow" title="Confira a página do livro <?php echo $titulo; ?> no Goodreads" style="font-weight:normal;">good<strong>reads</strong></a></span>
  <?php }  ?>
    <br /> 
     <?php endif;

        echo get_the_term_list( $post->ID, 'autor', 'de  ', ', ', '<br />' ); 
	 
       if ($editoras && ano){  
          echo get_the_term_list( $post->ID, 'editora', '<strong>Publicação:</strong>  ', ', ', '' ) . ' em ' .  $ano . '<br />'; 
        }    
  
	 if( $titulooriginal ): ?>
        <strong>Título Original: </strong><?php echo $titulooriginal; ?><br /> 
     <?php endif; 
	 
	 if( $seriedolivro ): ?>
        <strong>Série: </strong><?php  echo '<a href="'. $link .'">' . $seriedolivro[0]->name . '</a>' ; ?><br /> 
     <?php endif; 
	 
	 if( $isbn ): ?>
        <strong>ISBN: </strong><?php echo $isbn; ?><br /> 
     <?php endif; 
	 
	   if (count($generos) == 1) {  
		   echo get_the_term_list( $post->ID, 'genero', '<strong>Gênero:</strong>  ', ', ', '<br />' ); 
	   } else {
		   echo get_the_term_list( $post->ID, 'genero', '<strong>Gêneros:</strong>  ', ', ', '<br />' ); 
	   }
	 
	 if( $paginas ): ?>
        <strong>Páginas: </strong><?php echo $paginas; ?><br /> 
     <?php endif; 
	     
	 if( $traducao ): ?>
        <strong>Tradução: </strong><?php echo $traducao; ?><br /> 
     <?php endif; 
	 
	 if( $niveldificuldade ): ?>
        <strong>Nível do idioma:</strong><?php echo $niveldificuldade; ?><br /> 
     <?php endif; 
	 
	 if( $lancamentonobrasil ): ?>
        <strong>Lançamento no Brasil:</strong><?php echo $lancamentonobrasil; ?><br /> 
     <?php endif; ?>
     
     
<?php
 ///////////
 // EXEMPLAR RECEBIDO ATRAVÉS DE...
///////////
?>
<?php ;
     if($recebido == "Cortesia"): ?>
        <strong>Esse livro foi:</strong> Cortesia<br /> 
     <?php endif; 
	 
	 if($recebido == "Comprei"): ?>
        <strong>Esse livro foi:</strong> Comprado<br /> 
     <?php endif; 
	 
	 if($recebido == "Ganhei"): ?>
        <strong>Esse livro foi:</strong> Presente<br /> 
     <?php endif; 
	 
	 if($recebido == "Emprestado"): ?>
        <strong>Esse livro foi:</strong> Emprestado<br /> 
     <?php endif; 
	 
	 if($recebido == "Troca"): ?>
        <strong>Esse livro foi:</strong> Trocado<br /> 
     <?php endif; ?>
     
   
<?php
 // LOJAS
?>
<?php if (in_category(487)) : 
       $saraiva = get_post_meta($post->ID, "saraiva", true);
       $fnac = get_post_meta($post->ID, "fnac", true);
       $cultura = get_post_meta($post->ID, "cultura", true);
       $submarino = get_post_meta($post->ID, "submarino", true);
       $amazonquinta = get_post_meta($post->ID, "amazonquinta", true);
       $bookdepository = get_post_meta($post->ID, "bookdepository", true);
       $bwb = get_post_meta($post->ID, "bwb", true);
       $kobobook = get_post_meta($post->ID, "kobobook", true);
       $kindlebook = get_post_meta($post->ID, "kindlebook", true); ?>
<span class="lojasresenhas">
<?php if( $saraiva ): ?>
         <a href="<?php echo $saraiva; ?>" rel="nofollow" class="saraiva">Saraiva</a>	  
        <?php endif; 
		
		if( $fnac ): ?>
         <a href="<?php echo $fnac; ?>" rel="nofollow" class="fnac">Fnac</a>	 
        <?php endif; 
		
		if( $cultura ):
        ?>
         <a href="<?php echo $cultura; ?>" rel="nofollow" class="cultura">Cultura</a>	  
        <?php endif; 
		
		if( $bookdepository ):
        ?>
          <a href="<?php echo $bookdepository; ?>" rel="nofollow" class="bookdepository">Book Depository</a>	 
        <?php endif; 
		
		if( $amazonquinta ):
        ?>
         <a href="<?php echo $amazonquinta; ?>" rel="nofollow" class="amazon">Amazon</a>	  
        <?php endif; 
		
		if( $submarino ):
        ?>
         <a href="<?php echo $submarino; ?>" rel="nofollow" class="submarino">Submarino</a>	 
        <?php endif; 
		
		if( $bwb ):
        ?>
          <a href="<?php echo $bwb; ?>" rel="nofollow" class="bwb">Better World Books</a>	
        <?php endif; 
		
		if( $kobobook ):
        ?>
         <a href="<?php echo $kobobook; ?>" rel="nofollow" class="cultura">Kobo</a>	
		<?php endif; 

        if( $kindlebook ): ?>
	     <a href="<?php echo $kindlebook; ?>" rel="nofollow" class="amazon">Kindle</a>	
		<?php endif; ?>      
</span>

	<?php  if (( $saraiva ) || ( $fnac ) || ( $submarino ) || ( $amazonquinta ) || ( $bookdepository ) || ( $bwb ) || ( $kobobook ) || ( $kindlebook )) { ?>
		<span class="avisopubli">A compra pode render comissão ao blog.</span>
	<?php } ?> 
  
 <?php endif; //lojas ?>
	
</p> <?php  // fichatecnicain ?>
</div> <?php  // fichatecnica ?>

 </div> <?php // poster serie ?>
 </div> <?php //itemprop="review" ?>

  <?php endif; //if $imagem ?>