import React from "react";

import {{ $modelName }}Repository from "../../repositories/{{ $modelName }}Repository";
import columns from './_columns'
import info from "./_info";
import {withRouter} from 'react-router-dom'
import Show from "../CRUDBase/Show";

class {{ $modelName }}Show extends Show {

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

}

export default withRouter({{ $modelName }}Show);
