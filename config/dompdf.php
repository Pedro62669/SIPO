<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configurações
    |--------------------------------------------------------------------------
    |
    | Define alguns valores padrão. É possível adicionar todas as definições que
    | podem ser configuradas em dompdf_config.inc.php. Também é possível
    | sobrescrever todo o arquivo de configuração.
    |
    */
    'show_warnings' => false,   // Lança uma exceção para avisos do dompdf.

    'public_path' => null,  // Sobrescreve o caminho público, se necessário.

    /*
     * A fonte Dejavu Sans não possui glifos para algumas entidades convertidas;
     * desative se precisar exibir € e £.
     */
    'convert_entities' => true,

    'options' => [
        /**
         * Localização do diretório de fontes do DOMPDF.
         *
         * Diretório onde o DOMPDF armazenará fontes e métricas de fontes.
         * Observação: este diretório deve existir e ser gravável pelo processo do servidor web.
         * Observe a barra final no caminho.
         *
         * Observações sobre fontes:
         * Métricas adicionais de fontes .afm podem ser adicionadas executando load_font.php pela linha de comando.
         *
         * Apenas as fontes originais "Base 14" estão presentes em todos os visualizadores de PDF.
         * Fontes adicionais devem ser incorporadas ao arquivo PDF, caso contrário o PDF pode não
         * ser exibido corretamente. Isso pode aumentar significativamente o tamanho do arquivo,
         * a menos que a geração de subconjunto de fontes esteja ativada. Antes de incorporar uma
         * fonte, verifique seus direitos conforme a licença da fonte.
         *
         * Qualquer especificação de fonte no HTML de origem é traduzida para a fonte disponível
         * mais próxima dentro do diretório de fontes.
         *
         * As fontes "Base 14" do padrão PDF são:
         * Courier, Courier-Bold, Courier-BoldOblique, Courier-Oblique,
         * Helvetica, Helvetica-Bold, Helvetica-BoldOblique, Helvetica-Oblique,
         * Times-Roman, Times-Bold, Times-BoldItalic, Times-Italic,
         * Symbol, ZapfDingbats.
         */
        'font_dir' => storage_path('fonts'), // Recomendado pelo dompdf (https://github.com/dompdf/dompdf/pull/782).

        /**
         * Localização do diretório de cache de fontes do DOMPDF.
         *
         * Este diretório contém as métricas de fontes em cache usadas pelo DOMPDF.
         * Ele pode ser o mesmo diretório definido em DOMPDF_FONT_DIR.
         *
         * Observação: este diretório deve existir e ser gravável pelo processo do servidor web.
         */
        'font_cache' => storage_path('fonts'),

        /**
         * Localização de um diretório temporário.
         *
         * O diretório especificado deve ser gravável pelo processo do servidor web.
         * O diretório temporário é necessário para baixar imagens remotas e ao usar
         * o backend PDFLib.
         */
        'temp_dir' => sys_get_temp_dir(),

        /**
         * ==== IMPORTANTE ====
         *
         * "chroot" do dompdf: impede que o dompdf acesse arquivos do sistema ou outros
         * arquivos do servidor web. Todos os arquivos locais abertos pelo dompdf devem
         * estar em um subdiretório deste caminho. NÃO defina como '/', pois isso pode
         * permitir que um invasor use o dompdf para ler qualquer arquivo do servidor.
         * Este deve ser um caminho absoluto.
         * Esta validação é feita apenas na chamada pela linha de comando via dompdf.php,
         * mas não no uso direto da classe, como:
         * $dompdf = new DOMPDF(); $dompdf->load_html($htmldata); $dompdf->render(); $pdfdata = $dompdf->output();
         */
        'chroot' => realpath(base_path()),

        /**
         * Lista de protocolos permitidos.
         *
         * Protocolos e wrappers PHP permitidos em URIs, além das regras de validação
         * que determinam se um recurso pode ser carregado. Não há garantia de suporte
         * completo para os protocolos/wrappers especificados neste array.
         *
         * @var array
         */
        'allowed_protocols' => [
            'data://' => ['rules' => []],
            'file://' => ['rules' => []],
            'http://' => ['rules' => []],
            'https://' => ['rules' => []],
        ],

        /**
         * Validação do caminho de artefatos operacionais (logs e arquivos temporários).
         */
        'artifactPathValidation' => null,

        /**
         * @var string
         */
        'log_output_file' => null,

        /**
         * Indica se a geração de subconjunto de fontes deve ser ativada.
         */
        'enable_font_subsetting' => false,

        /**
         * Backend de renderização de PDF a ser usado.
         *
         * Valores válidos: 'PDFLib', 'CPDF' (classe PDF R&OS incluída), 'GD' e
         * 'auto'. 'auto' procurará PDFLib e a usará se encontrada; caso contrário,
         * usará CPDF. 'GD' renderiza PDFs como arquivos gráficos.
         * {@link * Canvas_Factory} determina qual classe de renderização será
         * instanciada com base nesta configuração.
         *
         * Os backends PDFLib e CPDF fornecem recursos de renderização suficientes
         * para o dompdf, mas recursos adicionais (como suporte a objetos, imagens e
         * fontes) diferem entre eles. Consulte {@link PDFLib_Adapter} para mais
         * informações sobre o backend PDFLib e {@link CPDF_Adapter} e lib/class.pdf.php
         * para mais informações sobre CPDF. Consulte também a documentação de cada
         * backend nos links abaixo.
         *
         * O backend GD é um pouco diferente de PDFLib e CPDF. Vários recursos de CPDF
         * e PDFLib não são suportados ou não fazem sentido ao criar arquivos de imagem.
         * Por exemplo, várias páginas não são suportadas, nem "objetos" PDF. Consulte
         * {@link GD_Adapter} para mais informações. O suporte a GD é experimental,
         * portanto use por sua conta e risco.
         *
         * @link http://www.pdflib.com
         * @link http://www.ros.co.nz/pdf
         * @link http://www.php.net/image
         */
        'pdf_backend' => 'CPDF',

        /**
         * Tipo de mídia de destino HTML que deve ser renderizado em PDF.
         * Lista de tipos e regras de análise para extensões futuras:
         * http://www.w3.org/TR/REC-html40/types.html
         *   screen, tty, tv, projection, handheld, print, braille, aural, all
         * Observação: aural está obsoleto no CSS 2.1 porque foi substituído por speech no CSS 3.
         * Mesmo que o PDF gerado seja destinado à impressão, o conteúdo desejado pode ser
         * diferente (por exemplo, visualização screen ou projection do arquivo HTML).
         * Portanto, permita a especificação do conteúdo aqui.
         */
        'default_media_type' => 'screen',

        /**
         * Tamanho padrão do papel.
         *
         * Na América do Norte, o padrão é "letter"; em outros países, geralmente "a4".
         *
         * @see CPDF_Adapter::PAPER_SIZES para tamanhos válidos ('letter', 'legal', 'A4', etc.)
         */
        'default_paper_size' => 'a4',

        /**
         * Orientação padrão do papel.
         *
         * Orientação da página (portrait ou landscape).
         *
         * @var string
         */
        'default_paper_orientation' => 'portrait',

        /**
         * Família de fonte padrão.
         *
         * Usada se nenhuma fonte adequada for encontrada. Ela deve existir na pasta de fontes.
         *
         * @var string
         */
        'default_font' => 'serif',

        /**
         * Configuração de DPI das imagens.
         *
         * Esta configuração determina o DPI padrão para imagens e fontes. O DPI pode
         * ser sobrescrito para imagens inline definindo explicitamente os atributos
         * de estilo de largura e altura da imagem. Por exemplo, se a largura nativa
         * da imagem for 600 pixels e você especificar a largura como 72 pontos,
         * a imagem terá DPI 600 no PDF renderizado. O DPI de imagens de fundo não
         * pode ser sobrescrito e é controlado inteiramente por este parâmetro.
         *
         * Para o DOMPDF, pixels por polegada (PPI) = pontos por polegada (DPI).
         * Se um tamanho em HTML for definido em px (ou sem unidade como tamanho de imagem),
         * esta opção informa o tamanho correspondente em pt.
         * Isso ajusta os tamanhos relativos para ficarem semelhantes à renderização
         * da página HTML em um navegador de referência.
         *
         * Em PDF, 1 pt sempre equivale a 1/72 polegada.
         *
         * Resolução de renderização de vários navegadores em px por polegada:
         * Windows Firefox e Internet Explorer:
         *   SystemControl->Display properties->FontResolution: Default:96, largefonts:120, custom:?
         * Linux Firefox:
         *   about:config *resolution: Default:96
         *   (dimensão da tela do xorg em mm e configurações de DPI da fonte no desktop são ignoradas)
         *
         * Tenha cuidado com fatores extras de zoom de fonte/imagem do navegador.
         *
         * Em imagens, o atributo de tamanho em pixels de <img> e o estilo CSS da imagem
         * sobrescrevem a dimensão real da imagem em px para a renderização.
         *
         * @var int
         */
        'dpi' => 96,

        /**
         * Ativa PHP incorporado.
         *
         * Se esta configuração for true, o DOMPDF avaliará automaticamente PHP incorporado
         * em tags <script type="text/php"> ... </script>.
         *
         * ==== IMPORTANTE ==== Ativar isso para documentos não confiáveis (por exemplo,
         * páginas HTML remotas arbitrárias) é um risco de segurança.
         * Scripts incorporados são executados com o mesmo nível de acesso ao sistema
         * disponível para o dompdf.
         * Defina esta opção como false (recomendado) se for processar documentos não confiáveis.
         * Esta configuração pode aumentar o risco de exploração do sistema.
         * Não altere esta configuração sem entender as consequências.
         * Documentação adicional está disponível na wiki do dompdf:
         * https://github.com/dompdf/dompdf/wiki
         *
         * @var bool
         */
        'enable_php' => false,

        /**
         * Ativa JavaScript inline.
         *
         * Se esta configuração for true, o DOMPDF inserirá automaticamente no PDF o código
         * JavaScript contido em tags <script type="text/javascript"> ... </script>, como escrito.
         * OBSERVAÇÃO: este é JavaScript baseado em PDF para execução pelo visualizador de PDF,
         * não JavaScript de navegador executado pelo Dompdf.
         *
         * @var bool
         */
        'enable_javascript' => true,

        /**
         * Ativa acesso a arquivos remotos.
         *
         * Se esta configuração for true, o DOMPDF acessará sites remotos para carregar
         * imagens e arquivos CSS conforme necessário.
         *
         * ==== IMPORTANTE ====
         * Isso pode ser um risco de segurança, especialmente em combinação com isPhpEnabled
         * e permitindo que HTML remoto seja passado para $dompdf = new DOMPDF(); $dompdf->load_html(...);
         * Isso permite que usuários anônimos baixem conteúdo duvidoso da internet que,
         * ao ser rastreado, parecerá ter sido baixado pelo seu servidor, ou permite que
         * código PHP malicioso em páginas HTML remotas seja executado pelo seu servidor
         * com os privilégios da sua conta.
         *
         * Esta configuração pode aumentar o risco de exploração do sistema. Não altere
         * esta configuração sem entender as consequências. Documentação adicional está
         * disponível na wiki do dompdf:
         * https://github.com/dompdf/dompdf/wiki
         *
         * @var bool
         */
        'enable_remote' => false,

        /**
         * Lista de hosts remotos permitidos.
         *
         * Cada valor do array deve ser um hostname válido.
         *
         * Esta lista será usada para filtrar quais recursos podem ser carregados em
         * combinação com isRemoteEnabled. Se enable_remote for FALSE, não terá efeito.
         *
         * Deixe como NULL para permitir qualquer host remoto.
         *
         * @var array|null
         */
        'allowed_remote_hosts' => null,

        /**
         * Proporção aplicada à altura das fontes para ficar mais parecida com a altura de linha dos navegadores.
         */
        'font_height_ratio' => 1.1,

        /**
         * Usa o parser HTML5 Lib.
         *
         * @deprecated Este recurso agora está sempre ativo no dompdf 2.x.
         *
         * @var bool
         */
        'enable_html5_parser' => true,
    ],

];
