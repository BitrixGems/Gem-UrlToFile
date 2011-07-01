<?php
/**
 * Адаптация плагина для symfony sfImageTransform к Битриксу
 *
 * @author Kalinin Alexey <hsalkaline@gmail.com>
 *
 */
class BitrixGem_UrlToFile extends BaseBitrixGem {

	protected $aGemInfo = array(
		'GEM' => 'UrlToFile',
		'AUTHOR' => 'Kalinin Alexey',
		'AUTHOR_LINK' => 'mailto:hsalkaline@gmail.com',
		'DATE' => '30.05.2011',
		'VERSION' => '0.1',
		'NAME' => 'UrlToFile',
		'DESCRIPTION' => "Добавление в загрузчик файлов в инфоблоках возможности загрузки файла по его URL",
		"REQUIRED_GEMS" => array('jQueryLoader', 'BitrixURLTools'),
		'REQUIRED_MIN_MODULE_VERSION' => '1.2.0',
	);

	public function event_main_OnProlog_addURLInput() {
		if ( defined( 'ADMIN_SECTION' ) ) {
			global $APPLICATION;
			$APPLICATION->AddHeadString( '
			<script type="text/javascript">
			var urlTools = new BitrixURLTools();
			if(urlTools.isIBlockElementEditPage()){
				$(function(){
					$("input[type=\'file\']").each(function(index,element){
						var item = $(element).clone()
						.attr("type", "text")
						.attr("id", $(element).attr("id") + "_URL")
						.insertAfter(element)
						$("<span>Или введите URL файла:</span>").insertBefore(item);
						var name = $(element).attr("name");
						var itemName = null;
						if(name.match(/PREVIEW_PICTURE/)){
							itemName="PROP[PREVIEW_PICTURE_BGURL]"
						}
						if(name.match(/DETAIL_PICTURE/)){
							itemName="PROP[DETAIL_PICTURE_BGURL]"
						}
						if(name.match(/PROP\[\d+\]\[(\w+)\]/)){
							itemName = name.replace(/PROP\[(\d+)\]\[(\w+)\]/, "PROP[$1][$2_BGURL]")
						}
						if(itemName){
							item.attr("name", itemName)
						}
					})
				});
			}
			</script>
			' );
		}
	}

	private function isValidFileArray( $aFileArray ) {
		return $aFileArray != null && $aFileArray['type'] != 'unknown';
	}

	private function getFileByUrl( &$aFields ) {
		$mPreviewPicture = CFile::MakeFileArray( $aFields['PROPERTY_VALUES']['PREVIEW_PICTURE_BGURL'] );
		if ( $this->isValidFileArray( $mPreviewPicture ) ) {
			$aFields['PREVIEW_PICTURE'] = $mPreviewPicture;
		}
		$mDetailPicture = CFile::MakeFileArray( $aFields['PROPERTY_VALUES']['DETAIL_PICTURE_BGURL'] );
		if ( $this->isValidFileArray( $mDetailPicture ) ) {
			$aFields['DETAIL_PICTURE'] = $mDetailPicture;
		}
		foreach ( $aFields['PROPERTY_VALUES'] as &$aProperty ) {
			if ( !is_array( $aProperty ) ) {
				continue;
			}
			foreach ( $aProperty as $sKey => $mValue ) {
				$aMatches = array();
				if ( preg_match( "/(\w+)_BGURL/", $sKey, &$aMatches ) > 0 ) {
					$mNewValue = CFile::MakeFileArray( $mValue['VALUE'] );
					if ( $this->isValidFileArray( $mNewValue ) ) {
						$iPropertyID = $aMatches[1];
						$aProperty[$iPropertyID]['VALUE'] = $mNewValue;
					}
				}
			}
		}
		return true;
	}

	public function event_iblock_OnStartIBlockElementAdd_getFileByUrl( &$aFields ) {
		return $this->getFileByUrl( $aFields );
	}

	public function event_iblock_OnStartIBlockElementUpdate_getFileByUrl( &$aFields ) {
		return $this->getFileByUrl( $aFields );
	}

}