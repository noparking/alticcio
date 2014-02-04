<?php

global $form, $object, $documents, $config, $dico, $hidden, $main, $documents;

$types_documents = $object->types_documents();
$langues = $object->langues();

if (count($documents)) {
	$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('LesDocuments'), 'class' => "produit-section produit-section-documents".$hidden['documents'], 'id' => "produit-section-documents-documents"))}
<table class="sortable" id="documents">
<thead>
<tr>
	<th>{$dico->t('Ordre')}</th>
	<th>{$dico->t('Vignette')}</th>
	<th>{$dico->t('Informations')}</th>
	<th>{$dico->t('Active')}</th>
	<th>{$dico->t('Public')}</th>
	<td></td>
</tr>
</thead>
<tbody>
HTML;
		$form_template = $form->template;
		$form->template = "#{field}";
		$documents_rows = array();
		foreach ($documents as $document) {
			$langue = $langues[$document['id_langues']];
			$type_document = $types_documents[$document['id_types_documents']];
			$order = $form->value("documents[{$document['id']}]") !== null ? $form->value("documents[{$document['id']}][classement]") : $document['classement'];
			$documents_rows[$order][] = <<<HTML
<tr>
	<td class="drag-handle"></td>
	<td><img class="produit-image" src="{$config->core_media("documents/".$document['vignette'])}" /></td>
	<td>
		{$document['titre']} ({$type_document}) [{$langue}]
		<br /><a target="_blank" href="{$config->core_media($document['fichier'], "pdf")}">{$dico->t("Telecharger")}</a>
	</td>
	<td>{$form->input(array('name' => "document[".$document['id']."][actif]", 'type' => "checkbox", 'checked' => $document['actif']))}</td>
	<td>{$form->input(array('name' => "document[".$document['id']."][public]", 'type' => "checkbox", 'checked' => $document['public']))}</td>
	<td>
		{$form->input(array('name' => "document[".$document['id']."][classement]", 'type' => "hidden", 'forced_value' => $order))}
		{$form->input(array('type' => "submit", 'name' => "delete-document[".$document['id']."]", 'class' => "delete", 'value' => "X"))}
	</td>
</tr>
HTML;
		}
		ksort($documents_rows);
		$flat_documents_rows = array();
		foreach ($documents_rows as $docs) {
			foreach ($docs as $doc) {
				$flat_documents_rows[] = $doc;
			}
		}
		$main .= implode("\n", $flat_documents_rows);
		$form->template = $form_template;
		$main .= <<<HTML
</tbody>
</table>
{$form->fieldset_end()}
HTML;
	}
	$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('AjouterUnDocument'), 'class' => "produit-section produit-section-documents".$hidden['documents'], 'id' => "produit-section-documents-new"))}
{$form->input(array('type' => "file", 'name' => "new_document_file", 'label' => $dico->t('Document') ))}
{$form->input(array('type' => "file", 'name' => "new_document_vignette", 'label' => $dico->t('DocumentVignette') ))}
{$form->input(array('name' => "new_document[classement]", 'type' => "hidden", 'forced_value' => $object->new_classement("documents_table")))}
{$form->input(array('type' => "text", 'name' => "new_document[titre]", 'label' => $dico->t('Titre')))}
{$form->select(array('name' => "new_document[id_langues]", 'label' => $dico->t("Langue"), 'options' => $langues))}
{$form->select(array('name' => "new_document[id_types_documents]", 'label' => $dico->t("TypeDocuments"), 'options' => $types_documents))}
{$form->input(array('type' => "submit", 'name' => "add-document", 'value' => $dico->t('Ajouter') ))}
{$form->fieldset_end()}
HTML;
