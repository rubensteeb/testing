####################################
### PLUGIN.TX_TESTMODELRELATIONS ###
####################################
plugin.tx_testmodelrelations.view {
	layoutRootPaths {
		0 = EXT:test_model_relations/Resources/Private/Layouts/Relations
	}
	templateRootPaths {
		0 = EXT:test_model_relations/Resources/Private/Templates/Relations
	}
	partialRootPaths {
		0 = EXT:test_model_relations/Resources/Private/Partials/Relations
	}
}
#########################
### CONFIG.TX_EXTBASE ###
#########################
config.tx_extbase.persistence.classes {
	RubenSteeb\TestModelRelations\Domain\Model\SuperClass {
		mapping {
			tableName = tx_testmodelrelations_domain_model_superclass
			recordType = RubenSteeb\TestModelRelations\Domain\Model\SuperClass
		}
		subclasses {
			RubenSteeb\TestModelRelations\Domain\Model\BaseClass = RubenSteeb\TestModelRelations\Domain\Model\BaseClass
		}
	}
	RubenSteeb\TestModelRelations\Domain\Model\BaseClass {
		mapping {
			tableName = tx_testmodelrelations_domain_model_superclass
			recordType = RubenSteeb\TestModelRelations\Domain\Model\BaseClass
		}
	}
	RubenSteeb\TestModelRelations\Domain\Model\RelationClass {
		mapping {
			tableName = tx_testmodelrelations_domain_model_relationclass
			recordType = RubenSteeb\TestModelRelations\Domain\Model\RelationClass
		}
		subclasses {
			RubenSteeb\TestModelRelations\Domain\Model\SecondRelationClass = RubenSteeb\TestModelRelations\Domain\Model\SecondRelationClass
		}
	}
	RubenSteeb\TestModelRelations\Domain\Model\SecondRelationClass {
		mapping {
			tableName = tx_testmodelrelations_domain_model_relationclass
			recordType = RubenSteeb\TestModelRelations\Domain\Model\SecondRelationClass
		}
	}
	RubenSteeb\TestModelRelations\Domain\Model\SecondLevelObjectWithStorage {
		mapping {
			tableName = tx_testmodelrelations_domain_model_secondlevelobjectwithstorage
			recordType = RubenSteeb\TestModelRelations\Domain\Model\SecondLevelObjectWithStorage
		}
	}
	RubenSteeb\TestModelRelations\Domain\Model\LastLevelObject {
		mapping {
			tableName = tx_testmodelrelations_domain_model_lastlevelobject
			recordType = RubenSteeb\TestModelRelations\Domain\Model\LastLevelObject
		}
	}
}