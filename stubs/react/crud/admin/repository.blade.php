import BaseRepository from "./BaseRepository";

class {{ $modelName }}Repository extends BaseRepository {
  constructor(){
    super();
    this.PATH = "/{{ $pathName }}";
  }
}

export default {{ $modelName }}Repository;
