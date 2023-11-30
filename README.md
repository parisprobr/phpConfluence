# PhpConfluence
Projeto para integrar a documentação de negócio atrelada ao código com o Confluence

## Tags utilizadas:
```
@confluence // Indica que a documentação deve ser enviada para o Confluence
@endconfluence //Indica o final da documentação
@space //Chave do espaço do time no Confluence. Para consultar a chave: http://confluence.local/spacedirectory/view.action
@title //Título da documentação
@content //Conteúdo da documentação
```

## Como utilizar
#### Adicionar um stage para o phpConfluence na pipeline
Exemplo:
```
phpConfluence:
  stage: phpConfluence
  image:
    name: ghcr.io/parisprobr/phpConfluence:latest
  script:
    - mkdir -p /data/
    - cp -r $CI_PROJECT_DIR/* /data/
    - cd /app/src 
    - php ExecutePipeline.php $GITLAB_USER_LOGIN $CONFLUENCE_USER $CONFLUENCE_PASS $CI_PROJECT_URL $CI_JOB_ID
    # ou 
    # - php ExecutePipeline.php $GITLAB_USER_LOGIN $CONFLUENCE_USER $CONFLUENCE_PASS $CI_MERGE_REQUEST_PROJECT_URL $CI_MERGE_REQUEST_IID
  only:
    - merge_requests
    - master
    - develop
```
#### No código:
Exemplo:
```
<?php

/**
* @confluence
* @space DF
* @title meu post do confluence
* @content
* conteúdo que irá para o meus post no confluence
*
* @endconfluence
**/
class MinhaClasseDeTeste{

  /**
  * @confluence
  * @space DF
  * @title meu post do confluence
  * @content
  * conteúdo que irá será concatenado no mesmo post acima , pois é mesmo titulo e space
  *
  * @endconfluence
  **/
  public function minha function()
  {

  }

}
>
```


As variáveis $GITLAB_USER_LOGIN, CI_MERGE_REQUEST_PROJECT_URL e $CI_MERGE_REQUEST_IID são utilizadas para compor a assinatura na documentação.

As variáveis $CONFLUENCE_USER e $CONFLUENCE_PASS correspondem ao usuário do Confluence que vai ser utilizado para gerenciar as documentações. Devem ser configuradas no Git para o grupo e/ou projeto que vai utilizar a documentação. 

