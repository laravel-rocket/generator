import React from "react";

import {{ $modelName }}Repository from "../../repositories/{{ $modelName }}Repository";
import columns from './_columns'
import info from "./_info";
import {withRouter} from 'react-router-dom'
import Edit from "../CRUDBase/Edit";
@foreach( array_unique($relations) as $relationModelName )
@if( $relationModelName !== $modelName )
import {{ $relationModelName }}Repository from "../../repositories/{{ $relationModelName }}Repository";
@endif
@endforeach

class {{ $modelName }}Edit extends Edit {

  setPageInfo() {
    this.title = info.title;
    this.path = info.path;
  }

  setRepository() {
    this.repository = new {{ $modelName }}Repository();
  }

  setColumnInfo() {
    this.columns = columns;
  }

  setRelationRepository() {
    this.relationRepositories = {
@foreach( $relations as $relationName => $relationModelName )
      "{{ $relationName }}": new {{ $relationModelName }}Repository(),
@endforeach
    };
  }
}

export default withRouter({{ $modelName }}Edit);
