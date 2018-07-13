<INCLUDE_TYPOSCRIPT: source="DIR:EXT:test_model_relations/Configuration/TypoScript" extensions="ts">





page = PAGE
page {
	typeNum = 0
	10 = FLUIDTEMPLATE
	10 {
		templateName = TEXT
		templateName.stdWrap.cObject = CASE
		templateName.stdWrap.cObject {
			key.data = pageLayout
			
			default = TEXT
			default.value = Default
		}
		
		templateRootPaths {
			0 = EXT:test_model_relations/Resources/Private/Templates/Page/
		}
		layoutRootPaths {
			0 = EXT:test_model_relations/Resources/Private/Layouts/Page/
		}
		partialRootPaths {
			0 = EXT:test_model_relations/Resources/Private/Partials/Page/
		}
	}
}


